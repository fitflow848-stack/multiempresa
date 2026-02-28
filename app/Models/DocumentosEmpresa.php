<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;
use App\Traits\BelongsToSucursal;

class DocumentosEmpresa extends Model
{
    protected $sucursalForeignKey = 'sucursal';
    use HasFactory, BelongsToCompany, BelongsToSucursal;
    

    protected $companyForeignKey = 'id_empresa';
        protected $table = 'documentos_empresas';

    protected $fillable = [
        'id_empresa',
        'id_tido',
        'sucursal',
        'serie',
        'numero',
    ];
}
