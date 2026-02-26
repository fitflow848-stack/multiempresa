<?php

namespace App\Models;

use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuiaRemision extends Model
{
    use HasFactory, BelongsToCompany;
    
    protected $table = 'guia_remision';

    protected $fillable = [
        'company_id',
        'branch_id',
        'ruc_partida',
        'razon_partida',
        'direccion_partida',
        'departamento_partida',
        'provincia_partida',
        'distrito_partida',
        'direccion_llegada',
        'departamento_llegada',
        'provincia_llegada',
        'distrito_llegada',
        'motivo_traslado',
        'observacion',
        'peso_bruto',
        'fecha_traslado',
        'serie',
        'numero',
        'documento_relacionado',
        'transportista_doc',
        'transportista_nombre',
        'transportista_mtc',
        'motivo_traslado_codigo',
        'modalidad_traslado_codigo',
        'nombre_archivo',
        'hash',
        'ticker',
        'sunat_status'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Sucursal::class, 'branch_id');
    }

    // Relación con Departamento (Partida)
    public function departamentoPartida()
    {
        return $this->belongsTo(Departamento::class, 'departamento_partida', 'dep_cod');
    }

    // Relación con Provincia (Partida)
    public function provinciaPartida()
    {
        return $this->belongsTo(Provincia::class, 'provincia_partida', 'pro_id');
    }

    // Relación con Distrito (Partida)
    public function distritoPartida()
    {
        return $this->belongsTo(Distrito::class, 'distrito_partida', 'dis_id');
    }

    // Relación con Departamento (Llegada)
    public function departamentoLlegada()
    {
        return $this->belongsTo(Departamento::class, 'departamento_llegada', 'dep_cod');
    }

    // Relación con Provincia (Llegada)
    public function provinciaLlegada()
    {
        return $this->belongsTo(Provincia::class, 'provincia_llegada', 'pro_id');
    }

    // Relación con Distrito (Llegada)
    public function distritoLlegada()
    {
        return $this->belongsTo(Distrito::class, 'distrito_llegada', 'dis_id');
    }
}
