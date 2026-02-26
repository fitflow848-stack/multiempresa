<?php

namespace App\Models;

use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoActivo extends Model
{
    use HasFactory, BelongsToCompany;
    
    protected $table = 'tipo_activos';
    protected $fillable = [
        'company_id','nombre', 'descripcion'];

    public function activos()
    {
        return $this->hasMany(ActivoFijo::class);
    }
}
