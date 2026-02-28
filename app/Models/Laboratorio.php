<?php

namespace App\Models;
use App\Traits\BelongsToCompany;



use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laboratorio extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'laboratorios';

    protected $fillable = [
        'company_id',
        'nombre',
        // agrega otros campos si los necesitas (p. ej. slug, descripcion)
    ];
}
