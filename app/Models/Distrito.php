<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    use HasFactory;

    protected $table = 'dir_distrito';
    protected $primaryKey = 'dis_id';
    protected $fillable = [
        'dis_nombre',
        'dis_codigo',
        'pro_cod',
        'dep_codigo',
    ];

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'pro_cod', 'pro_cod');
    }
    
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'dep_codigo', 'dep_cod');
    }
}
