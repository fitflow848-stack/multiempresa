<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CierreCaja extends Model
{
    use HasFactory;

    protected $table = 'cierre_cajas';

    protected $fillable = [
        'user_id',
        'fecha_cierre',
        'monto_apertura',
        'monto_cierre',
        'ingresos',
        'egresos',
        'observaciones',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
