<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoActivoCorriente extends Model
{
    protected $fillable = ['nombre', 'descripcion'];

    public function activos()
    {
        return $this->hasMany(ActivoCorriente::class);
    }
}
