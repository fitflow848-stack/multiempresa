<?php

namespace App\Models;

use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Familia extends Model
{
    use HasFactory, BelongsToCompany;
    
    protected $fillable = [
        'company_id','nombre'];

    // Relaciones
    public function subfamilias()
    {
        return $this->hasMany(SubFamilia::class);
    }
}