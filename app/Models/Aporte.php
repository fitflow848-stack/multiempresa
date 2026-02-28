<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Aporte extends Model
{
    use HasFactory, BelongsToCompany;
    
    protected $fillable = [
        'company_id',
        'tipo_aporte_id',
        'nombre',
        'monto',
        'fecha_registro',
        'documento',
        'observaciones',
        'user_id'
    ];

    protected $casts = [
        'fecha_registro' => 'date',
        'monto' => 'decimal:2'
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoAporte::class, 'tipo_aporte_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
