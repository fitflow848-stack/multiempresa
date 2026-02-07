<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoPasivo extends Model
{
    protected $fillable = ['nombre', 'descripcion'];

    public function pasivos()
    {
        return $this->hasMany(Pasivo::class);
    }
}
