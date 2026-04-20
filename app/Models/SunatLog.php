<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SunatLog extends Model
{
    protected $table = 'sunat_logs';

    protected $fillable = [
        'venta_id',
        'documento_identificador',
        'status',
        'message',
        'response_data',
        'type',
    ];

    protected $casts = [
        'response_data' => 'array',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
