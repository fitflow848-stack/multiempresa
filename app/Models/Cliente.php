<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
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
        return $this->hasMany('App\Models\Venta');
    }

    public function getDebeAttribute()
    {
        // Calcular el monto que debe el cliente
        // Esto se puede implementar basado en las ventas pendientes
        return 0.00;
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