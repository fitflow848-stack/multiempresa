<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;
use App\Traits\BelongsToSucursal;

class Compra extends Model
{
    protected $sucursalForeignKey = 'local_destino';
    use HasFactory, BelongsToCompany, BelongsToSucursal;
    
    
    protected $fillable = [
        'company_id',
        'proveedor_id', 'fecha_emision', 'fecha_pago', 'moneda', 'credito', 'percepcion', 'inc_impuesto',
        'total_bruto', 'total_descuento', 'bruto_neto', 'total_impuesto', 'total_neto', 'flete', 'total_pagar',
        'tipo', 'serie_comprobante', 'numero_comprobante', 'presupuesto', 'local_destino', 'received_at', 'recibido', 'id_usuario'
    ];

    protected $casts = [
        'credito' => 'boolean',
        'percepcion' => 'boolean',
        'inc_impuesto' => 'boolean',
        'received_at' => 'datetime',
    ];

    public function lineas()
    {
        return $this->hasMany(CompraLinea::class, 'compra_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function almacen()
    {
        return $this->belongsTo(Sucursal::class, 'local_destino');
    }
}