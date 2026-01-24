<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $table = 'dir_departamento';
    protected $primaryKey = 'dep_id';
    protected $fillable = [
        'dep_nombre',
        'dep_cod',
    ];

    public function provincias()
    {
        return $this->hasMany(Provincia::class, 'dep_codigo', 'dep_cod');
    }
}
