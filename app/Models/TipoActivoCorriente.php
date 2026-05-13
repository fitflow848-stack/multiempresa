<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class TipoActivoCorriente extends Model
{
    use HasFactory, BelongsToCompany;
    
    protected $fillable = [
        'company_id', 'nombre', 'descripcion', 'afecta_caja'];

    protected $casts = [
        'afecta_caja' => 'boolean',
    ];

    public function activos()
    {
        return $this->hasMany(ActivoCorriente::class);
    }
}
