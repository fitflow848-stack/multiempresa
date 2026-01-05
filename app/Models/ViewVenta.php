<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewVenta extends Model
{
    use HasFactory;
    protected $table = 'view_ventas'; // Nombre de la vista en la base de datos
    public $timestamps = false; // Evita errores con timestamps
}
