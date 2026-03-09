<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    use HasFactory;

    /**
     * Eliminación en cascada de todos los datos de la empresa.
     * El orden respeta las restricciones de foreign key:
     * primero los registros más dependientes, luego los contenedores.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Company $company) {
            DB::transaction(function () use ($company) {
                $companyId = $company->id;

                // ── Obtener IDs de apoyo ─────────────────────────────────────
                $userIds        = DB::table('users')->where('company_id', $companyId)->pluck('id');
                $sucursalIds    = DB::table('sucursales')->where('company_id', $companyId)->pluck('id');
                $cajaIds        = DB::table('cajas')->where('company_id', $companyId)->pluck('id');
                $cierreCajaIds  = DB::table('cierre_cajas')->where('id_empresa', $companyId)->pluck('id');
                $ventaIds       = DB::table('ventas')->where('id_empresa', $companyId)->pluck('id_venta');
                $compraIds      = DB::table('compras')->where('company_id', $companyId)->pluck('id');
                $ingresoIds     = DB::table('almacen_ingresos')->where('empresa_id', $companyId)->pluck('id');
                $deudaIds       = DB::table('deudas')->where('company_id', $companyId)->pluck('id');
                $pasivoIds      = DB::table('pasivos')->where('company_id', $companyId)->pluck('id');
                $cotizacionIds  = DB::table('cotizaciones')
                    ->where('company_id', $companyId)
                    ->when($userIds->isNotEmpty(), function ($query) use ($userIds) {
                        return $query->orWhereIn('usuario_id', $userIds);
                    })->pluck('id');
                $guiaIds        = DB::table('guia_remision')->where('company_id', $companyId)->pluck('id');

                // 1. Registros que referencian cierre_cajas (más profundos)
                if ($cierreCajaIds->isNotEmpty()) {
                    DB::table('pasivos')->whereIn('cierre_caja_id', $cierreCajaIds)->delete();
                    DB::table('arqueo_cajas')->whereIn('cierre_id', $cierreCajaIds)->delete();
                    DB::table('operaciones_caja')->whereIn('cierre_caja_id', $cierreCajaIds)->delete();
                }

                // 2. Pagos de deudas y pasivos
                if ($deudaIds->isNotEmpty()) {
                    DB::table('deuda_pagos')->whereIn('deuda_id', $deudaIds)->delete();
                }
                if ($pasivoIds->isNotEmpty()) {
                    DB::table('pasivo_pagos')->whereIn('pasivo_id', $pasivoIds)->delete();
                }
                if ($userIds->isNotEmpty()) {
                    DB::table('pasivo_pagos')->whereIn('user_id', $userIds)->delete();
                    DB::table('deuda_pagos')->whereIn('user_id', $userIds)->delete();
                }

                // 3. Detalles de ventas
                if ($ventaIds->isNotEmpty()) {
                    DB::table('venta_detalles')->whereIn('id_venta', $ventaIds)->delete();
                    DB::table('ventas_sunat')->whereIn('id_venta', $ventaIds)->delete();
                }

                // 4. Líneas de compras y detalles de almacén
                if ($compraIds->isNotEmpty()) {
                    DB::table('compra_lineas')->whereIn('compra_id', $compraIds)->delete();
                }
                if ($ingresoIds->isNotEmpty()) {
                    DB::table('almacen_ingreso_detalle')->whereIn('ingreso_id', $ingresoIds)->delete();
                }

                // 5. Detalles de cotizaciones y guías
                if ($cotizacionIds->isNotEmpty()) {
                    DB::table('cotizacion_detalles')->whereIn('cotizacion_id', $cotizacionIds)->delete();
                }
                if ($guiaIds->isNotEmpty()) {
                    DB::table('guia_remision_producto')->whereIn('id_guia', $guiaIds)->delete();
                    DB::table('guia_destinatario')->whereIn('id_guia', $guiaIds)->delete();
                }

                // 6. Tablas principales de operaciones
                DB::table('ventas')->where('id_empresa', $companyId)->delete();
                DB::table('deudas')->where('company_id', $companyId)->delete();
                DB::table('compras')->where('company_id', $companyId)->delete();
                if ($userIds->isNotEmpty()) {
                    DB::table('cotizaciones')->whereIn('usuario_id', $userIds)->delete();
                }
                DB::table('cotizaciones')->where('company_id', $companyId)->delete();
                DB::table('guia_remision')->where('company_id', $companyId)->delete();
                DB::table('almacen_ingresos')->where('empresa_id', $companyId)->delete();
                if ($sucursalIds->isNotEmpty()) {
                    DB::table('almacen_transferencias')->whereIn('sucursal_origen_id', $sucursalIds)->delete();
                }
                DB::table('aportes')->where('company_id', $companyId)->delete();

                // 7. Módulos financieros (nombres REALES de tablas en BD)
                DB::table('pasivos')->where('company_id', $companyId)->delete();
                DB::table('activo_corrientes')->where('company_id', $companyId)->delete();
                DB::table('activos_fijos')->where('company_id', $companyId)->delete();

                // 8. Cierre_cajas y cajas
                DB::table('cierre_cajas')->where('id_empresa', $companyId)->delete();
                if ($cajaIds->isNotEmpty()) {
                    DB::table('caja_user')->whereIn('caja_id', $cajaIds)->delete();
                }
                DB::table('cajas')->where('company_id', $companyId)->delete();

                // 9. Clientes, proveedores, productos
                DB::table('clientes')->where('company_id', $companyId)->delete();
                DB::table('proveedores')->where('id_empresa', $companyId)->delete();
                DB::table('productos')->where('id_empresa', $companyId)->delete();

                // 10. Catálogos de la empresa (nombres REALES de tablas en BD)
                DB::table('tipo_activos')->where('company_id', $companyId)->delete();
                DB::table('tipo_activo_corrientes')->where('company_id', $companyId)->delete();
                DB::table('tipo_pasivos')->where('company_id', $companyId)->delete();
                DB::table('tipo_aportes')->where('company_id', $companyId)->delete();
                DB::table('marcas')->where('company_id', $companyId)->delete();
                DB::table('laboratorios')->where('company_id', $companyId)->delete();
                DB::table('unidades_medida')->where('company_id', $companyId)->delete();
                DB::table('presentaciones')->where('company_id', $companyId)->delete();
                DB::table('concentraciones')->where('company_id', $companyId)->delete();

                // 11. Sucursales y usuarios
                if ($sucursalIds->isNotEmpty()) {
                    DB::table('branch_user')->whereIn('sucursal_id', $sucursalIds)->delete();
                }
                DB::table('sucursales')->where('company_id', $companyId)->delete();
                DB::table('users')->where('company_id', $companyId)->delete();

                // 12. Documentos y configuración de la empresa
                DB::table('company_documents')->where('company_id', $companyId)->delete();
                DB::table('documentos_empresas')->where('id_empresa', $companyId)->delete();
            });
        });
    }

    protected $fillable = [
        'ruc',
        'razon_social',
        'nombre_comercial',
        'tipo_contribuyente',
        'logo', // Campo para el logo de la empresa

        'rep_nombre',
        'rep_document_type',
        'rep_document_number',
        'rep_cargo',
        'rep_email',
        'rep_phone',

        'department',
        'province',
        'district',
        'direccion_fiscal',
        'ubigeo',

        'email',
        'phone',
        'website',

        'regimen',
        'afecto_igv',
        'porcentaje_igv',

        'sol_user',
        'sol_password',
        'sunat_client_id',
        'sunat_client_secret',
        'sunat_local_code',
        'ose_provider',
        'ose_user',
        'ose_password',
        'ose_url',

        'serie_factura',
        'serie_boleta',
        'serie_nota_credito',
        'serie_nota_debito',

        'cert_file',
        'cert_password',
        'cert_expires_at',

        'bank',
        'account_type',
        'account_number',
        'cci',

        'is_active',
        'fecha_alta',
        'observations',
        'ticket_footer_message',
    ];

    protected $casts = [
        'afecto_igv' => 'boolean',
        'is_active' => 'boolean',
        'porcentaje_igv' => 'decimal:2',
        'cert_expires_at' => 'datetime',
        'fecha_alta' => 'datetime',
        // Comentamos temporalmente la encriptación automática para evitar errores en Filament
        // 'sol_password' => 'encrypted',
        // 'ose_password' => 'encrypted', 
        // 'cert_password' => 'encrypted',
    ];

    /**
     * Usuarios que pertenecen a esta empresa
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Sucursales (branches) de la empresa
     */
    public function sucursales(): HasMany
    {
        return $this->hasMany(Sucursal::class, 'company_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CompanyDocument::class);
    }

    /**
     * Obtener la URL completa del logo
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        // Si el logo ya es una URL completa, devolverla tal como está
        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }

        // Si es una ruta relativa, construir la URL completa
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

    /**
     * Métodos para manejar encriptación manual si es necesario
     */
    public function setSolPasswordAttribute($value)
    {
        // Si el valor no está vacío, lo guardamos tal como está
        // Puedes agregar encriptación manual aquí si lo necesitas en el futuro
        $this->attributes['sol_password'] = $value;
    }

    public function setOsePasswordAttribute($value)
    {
        // Si el valor no está vacío, lo guardamos tal como está  
        // Puedes agregar encriptación manual aquí si lo necesitas en el futuro
        $this->attributes['ose_password'] = $value;
    }

    public function setCertPasswordAttribute($value)
    {
        // Si el valor no está vacío, lo guardamos tal como está
        // Puedes agregar encriptación manual aquí si lo necesitas en el futuro
        $this->attributes['cert_password'] = $value;
    }
}
