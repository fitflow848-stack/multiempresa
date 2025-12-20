<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Familia extends Model
{
    use HasFactory;

    protected $fillable = ['nombre'];

    // Relaciones
    public function subfamilias()
    {
        return $this->hasMany(SubFamilia::class);
    }
}