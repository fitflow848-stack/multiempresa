<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosVentaGuardada extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'pos_ventas_guardadas';

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'cliente_nombre',
        'total',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Sucursal::class, 'branch_id');
    }
}
