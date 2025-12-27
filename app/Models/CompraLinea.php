<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraLinea extends Model
{
    use HasFactory;

    protected $table = 'compra_lineas';

    protected $fillable = [
        'compra_id','product_id','cb','descripcion','cantidad','costo','descuento','vcpc'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'costo' => 'decimal:2',
        'descuento' => 'decimal:2',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'product_id');
    }   
}