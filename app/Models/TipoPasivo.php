<?php

namespace App\Models;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoPasivo extends Model
{
    use HasFactory, BelongsToCompany;
    

    protected $fillable = ['company_id', 'nombre', 'descripcion'];

    public function pasivos()
    {
        return $this->hasMany(Pasivo::class);
    }
}
