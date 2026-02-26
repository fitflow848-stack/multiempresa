<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoPasivo extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'descripcion'];

    public function pasivos()
    {
        return $this->hasMany(Pasivo::class);
    }
}
