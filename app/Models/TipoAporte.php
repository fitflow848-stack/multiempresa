<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class TipoAporte extends Model
{
    use HasFactory, BelongsToCompany;
    
    protected $fillable = [
        'company_id','nombre', 'descripcion'];

    public function aportes()
    {
        return $this->hasMany(Aporte::class);
    }
}
