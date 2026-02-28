<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\BelongsToSucursal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperacionCaja extends Model
{
    use HasFactory, BelongsToCompany, BelongsToSucursal;

    protected $table = 'operaciones_caja';

    protected $fillable = [
        'company_id',
        'sucursal_id',
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
