<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subfamilia extends Model
{
    use HasFactory;

    protected $fillable = ['familia_id', 'nombre'];

    public function familia()
    {
        return $this->belongsTo(Familia::class);
    }
}