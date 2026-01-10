<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concentracion extends Model
{
    use HasFactory;

    protected $table = 'concentraciones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'unidad',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Scope para obtener solo activos
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    // Relación con productos
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}