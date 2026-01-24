<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuiaDestinatario extends Model
{
    use HasFactory;

    protected $table = 'guia_destinatario';
    public $timestamps = false;
    protected $fillable = [
        'id_guia',
        'documento',
        'datos',
    ];
}
