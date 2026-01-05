<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laboratorio extends Model
{
    use HasFactory;

    protected $table = 'laboratorios';

    protected $fillable = [
        'nombre',
        // agrega otros campos si los necesitas (p. ej. slug, descripcion)
    ];
}
