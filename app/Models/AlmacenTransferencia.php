<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlmacenTransferencia extends Model
{
    use HasFactory;

    protected $table = 'almacen_transferencias';

    protected $fillable = [
        'codigo_transferencia',
        'producto_id',
        'origen_lote_id',
        'destino_lote_id',
        'sucursal_origen_id',
        'sucursal_destino_id',
        'cantidad',
        'user_id',
        'observaciones',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function origenLote()
    {
        return $this->belongsTo(AlmacenIngresoDetalle::class, 'origen_lote_id');
    }

    public function destinoLote()
    {
        return $this->belongsTo(AlmacenIngresoDetalle::class, 'destino_lote_id');
    }

    public function sucursalOrigen()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_origen_id');
    }

    public function sucursalDestino()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_destino_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
