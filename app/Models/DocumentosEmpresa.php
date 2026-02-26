<?php

namespace App\Models;

use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentosEmpresa extends Model
{
    use HasFactory, BelongsToCompany;

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
