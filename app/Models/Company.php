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
        'logo', // Campo para el logo de la empresa

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
        'sunat_client_id',
        'sunat_client_secret',
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
        'ticket_footer_message',
    ];

    protected $casts = [
        'afecto_igv' => 'boolean',
        'is_active' => 'boolean',
        'porcentaje_igv' => 'decimal:2',
        'cert_expires_at' => 'datetime',
        'fecha_alta' => 'datetime',
        // Comentamos temporalmente la encriptación automática para evitar errores en Filament
        // 'sol_password' => 'encrypted',
        // 'ose_password' => 'encrypted', 
        // 'cert_password' => 'encrypted',
    ];

    /**
     * Usuarios que pertenecen a esta empresa
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Sucursales (branches) de la empresa
     */
    public function sucursales(): HasMany
    {
        return $this->hasMany(Sucursal::class, 'company_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CompanyDocument::class);
    }

    /**
     * Obtener la URL completa del logo
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        // Si el logo ya es una URL completa, devolverla tal como está
        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }

        // Si es una ruta relativa, construir la URL completa
        return asset('storage/' . $this->logo);
    }

    /**
     * Obtener la ruta completa del archivo del logo para uso interno
     */
    public function getLogoPathAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        return storage_path('app/public/' . $this->logo);
    }

    /**
     * Métodos para manejar encriptación manual si es necesario
     */
    public function setSolPasswordAttribute($value)
    {
        // Si el valor no está vacío, lo guardamos tal como está
        // Puedes agregar encriptación manual aquí si lo necesitas en el futuro
        $this->attributes['sol_password'] = $value;
    }

    public function setOsePasswordAttribute($value)
    {
        // Si el valor no está vacío, lo guardamos tal como está  
        // Puedes agregar encriptación manual aquí si lo necesitas en el futuro
        $this->attributes['ose_password'] = $value;
    }

    public function setCertPasswordAttribute($value)
    {
        // Si el valor no está vacío, lo guardamos tal como está
        // Puedes agregar encriptación manual aquí si lo necesitas en el futuro
        $this->attributes['cert_password'] = $value;
    }
}
