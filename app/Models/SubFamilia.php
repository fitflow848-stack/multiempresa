<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubFamilia extends Model
{
    use HasFactory;

    protected $table = 'subfamilias';
    protected $fillable = ['familia_id', 'nombre'];

    public function familia()
    {
        return $this->belongsTo(Familia::class);
    }
}