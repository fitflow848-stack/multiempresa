<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruc',
        'razon_social',
        'nombre_comercial',
        'tipo_contribuyente',

        'rep_nombre',
        'rep_document_type',
        'rep_document_number',
        'rep_cargo',
        'rep_email',
        'rep_phone',

        'department',
        'province',
        'district',
        'direccion_fiscal',
        'ubigeo',

        'email',
        'phone',
        'website',

        'regimen',
        'afecto_igv',
        'porcentaje_igv',

        'sol_user',
        'sol_password',
        'sunat_local_code',
        'ose_provider',
        'ose_user',
        'ose_password',
        'ose_url',

        'serie_factura',
        'serie_boleta',
        'serie_nota_credito',
        'serie_nota_debito',

        'cert_file',
        'cert_password',
        'cert_expires_at',

        'bank',
        'account_type',
        'account_number',
        'cci',

        'is_active',
        'fecha_alta',
        'observations',
    ];

    protected $casts = [
        'afecto_igv' => 'boolean',
        'is_active' => 'boolean',
        'porcentaje_igv' => 'decimal:2',
        'cert_expires_at' => 'datetime',
        'fecha_alta' => 'datetime',
        'sol_password' => 'encrypted',
        'ose_password' => 'encrypted',
        'cert_password' => 'encrypted',
    ];


    /**
     * Usuarios que pertenecen a esta empresa
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
