<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'sucursal_id',
        'tipo_cliente',
        'tipo_documento',
        'numero_documento',
        'nombre',
        'direccion',
        'distrito',
        'provincia',
        'departamento',
        'telefono',
        'email',
        'credito_limite',
        'estado',
        'observaciones'
    ];

    protected $casts = [
        'credito_limite' => 'decimal:2',
        'estado' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_cliente');
    }

    public function deudas()
    {
        return $this->hasMany(Deuda::class, 'cliente_id');
    }

    public function getDebeAttribute()
    {
        if ($this->relationLoaded('deudas')) {
            return $this->deudas
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->sum('monto_deuda');
        }

        return $this->deudas()->whereIn('estado', ['pendiente', 'parcial'])->sum('monto_deuda') ?: 0;
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', true);
    }

    public function scopePorTipoDocumento($query, $tipo)
    {
        return $query->where('tipo_documento', $tipo);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
                ->orWhere('numero_documento', 'like', "%{$termino}%");
        });
    }
}