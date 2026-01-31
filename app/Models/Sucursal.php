<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'company_id',
        'nombre',
        'direccion',
        'telefono',
    ];

    public function documents()
    {
        return $this->hasMany(CompanyDocument::class, 'branch_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
