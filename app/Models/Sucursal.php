<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Sucursal extends Model
{
    use HasFactory, BelongsToCompany;

    /**
     * Al eliminar una sucursal, se borra también toda su información
     * operativa (ventas, compras, ingresos de almacén, activos/adelantos
     * y ventas guardadas del POS asignados a ella).
     *
     * `cajas`, `company_documents` y `branch_user` ya cascadean solos por
     * FK (cascadeOnDelete), y `users.branch_id` / `banco_movimientos.sucursal_id`
     * quedan en NULL por FK (nullOnDelete) — no hace falta tocarlos aquí.
     *
     * Nota: `cierre_cajas`, `operaciones_caja`, `pasivos`, `clientes`,
     * `proveedores` y `cotizaciones` tienen una columna `sucursal_id` sin
     * FK que en una migración antigua se rellenó con el valor 1 para TODOS
     * los registros existentes (sin distinguir empresa/sucursal real), por
     * lo que no es un dato confiable para cascadear un borrado — se dejan
     * intactos a propósito para no arriesgar borrar datos de otra sucursal.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Sucursal $sucursal) {
            DB::transaction(function () use ($sucursal) {
                $sucursalId = $sucursal->id;

                $ventaIds = DB::table('ventas')->where('sucursal', $sucursalId)->pluck('id_venta');
                $compraIds = DB::table('compras')->where('local_destino', $sucursalId)->pluck('id');
                $ingresoIds = DB::table('almacen_ingresos')->where('sucursal_id', $sucursalId)->pluck('id');

                if ($ventaIds->isNotEmpty()) {
                    DB::table('venta_detalles')->whereIn('id_venta', $ventaIds)->delete();
                    DB::table('ventas_sunat')->whereIn('id_venta', $ventaIds)->delete();
                }
                if ($compraIds->isNotEmpty()) {
                    DB::table('compra_lineas')->whereIn('compra_id', $compraIds)->delete();
                }
                if ($ingresoIds->isNotEmpty()) {
                    $detalleIds = DB::table('almacen_ingreso_detalle')->whereIn('ingreso_id', $ingresoIds)->pluck('id');
                    if ($detalleIds->isNotEmpty()) {
                        // RESTRICT por FK: una transferencia entre sucursales
                        // referencia el lote de origen y de destino.
                        DB::table('almacen_transferencias')
                            ->where(function ($q) use ($detalleIds) {
                                $q->whereIn('origen_lote_id', $detalleIds)
                                  ->orWhereIn('destino_lote_id', $detalleIds);
                            })
                            ->delete();
                    }
                    DB::table('almacen_ingreso_detalle')->whereIn('ingreso_id', $ingresoIds)->delete();
                }

                DB::table('ventas')->where('sucursal', $sucursalId)->delete();
                DB::table('compras')->where('local_destino', $sucursalId)->delete();
                DB::table('almacen_ingresos')->where('sucursal_id', $sucursalId)->delete();
                DB::table('pos_ventas_guardadas')->where('branch_id', $sucursalId)->delete();
                // RESTRICT por FK: hay que borrarlos antes de poder eliminar la sucursal.
                DB::table('activo_corrientes')->where('sucursal_id', $sucursalId)->delete();
                DB::table('activos_fijos')->where('sucursal_id', $sucursalId)->delete();
            });
        });
    }

    protected $table = 'sucursales';

    protected $fillable = [
        'company_id',
        'nombre',
        'direccion',
        'telefono',
        'logo',
        'numero_cajas',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'numero_cajas' => 'integer',
    ];

    // ─── Relaciones ─────────────────────────────────────────────

    /**
     * Empresa a la que pertenece esta sucursal
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Documentos de la sucursal
     */
    public function documents(): HasMany
    {
        return $this->hasMany(CompanyDocument::class, 'branch_id');
    }

    /**
     * Cajas de esta sucursal
     */
    public function cajas(): HasMany
    {
        return $this->hasMany(Caja::class);
    }

    /**
     * Usuarios asignados a esta sucursal
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    // ─── Accessors ──────────────────────────────────────────────

    /**
     * Obtener la URL completa del logo de la sucursal
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            // Fallback: usar el logo de la empresa
            return $this->company?->logo_url;
        }

        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }

        return asset('storage/' . $this->logo);
    }

    /**
     * Obtener la ruta completa del archivo del logo para uso interno
     */
    public function getLogoPathAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        return storage_path('app/public/' . $this->logo);
    }

    // ─── Scopes ─────────────────────────────────────────────────

    /**
     * Scope para sucursales activas
     */
    public function scopeActivas($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para sucursales de una empresa
     */
    public function scopeDeEmpresa($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}

