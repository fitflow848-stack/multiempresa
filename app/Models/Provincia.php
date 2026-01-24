<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    use HasFactory;

    protected $table = 'dir_provincia';
    protected $primaryKey = 'pro_id';
    protected $fillable = [
        'pro_nombre',
        'dep_codigo',
        'pro_cod',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'dep_codigo', 'dep_cod');
    }

    public function distritos()
    {
        return $this->hasMany(Distrito::class, 'pro_cod', 'pro_cod');
    }
}
