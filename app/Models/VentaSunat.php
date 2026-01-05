<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaSunat extends Model
{
    use HasFactory;

    protected $table = "ventas_sunat";
    
     // Usar id_venta como primary key
     protected $primaryKey = 'id_venta';

     // Si id_venta NO es AUTO_INCREMENT:
     public $incrementing = false;
 
     // Si id_venta es integer
     protected $keyType = 'int';

    protected $fillable = [
        'id_venta',
        'hash',
        'nombre_xml',
        'qr_data',
        'content_xml',
    ];
}
