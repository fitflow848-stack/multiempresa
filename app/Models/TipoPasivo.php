<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoPasivo extends Model
{
    use HasFactory;
    

    protected $fillable = ['nombre', 'descripcion'];

    public function pasivos()
    {
        return $this->hasMany(Pasivo::class);
    }
}
