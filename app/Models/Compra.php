<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'proveedor_id','fecha_emision','fecha_pago','moneda','credito','percepcion','inc_impuesto',
        'total_bruto','total_descuento','bruto_neto','total_impuesto','total_neto','flete','total_pagar',
        'tipo','presupuesto','local_destino','received_at'
    ];

    protected $casts = [
        'credito' => 'boolean',
        'percepcion' => 'boolean',
        'inc_impuesto' => 'boolean',
        'received_at' => 'datetime',
    ];

    public function lineas()
    {
        return $this->hasMany(CompraLinea::class, 'compra_id');
    }
}