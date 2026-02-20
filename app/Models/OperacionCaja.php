<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperacionCaja extends Model
{
    use HasFactory;

    protected $table = 'operaciones_caja';

    protected $fillable = [
        'cierre_caja_id',
        'user_id',
        'tipo',
        'partida',
        'concepto',
        'importe',
        'es_efectivo',
        'metodo_pago'
    ];

    public function cierre()
    {
        return $this->belongsTo(CierreCaja::class, 'cierre_caja_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
