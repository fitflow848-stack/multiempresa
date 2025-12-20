<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'id_empresa',
        'nombre',
        'laboratorio',
        'familia_id',
        'subfamilia_id',
        'marca_id',
        'unidad_medida_id',
        'tipo_impuesto',
        'opciones_avanzadas',
        'condicion_venta',
        'attr_numero_serie',
        'attr_fecha_vencimiento',
        'attr_lote_produccion',
        'attr_venta_menudeo',
        'codigo_barras',
        'registro_sanitario',
        'presentacion_modelo',
        'concentracion_detalle',
        'cantidad',
        'precio_compra',
        'pvp',
        'pv_docena',
        'pvp_dto',
        'pvc',
        'pvc_dto',
        'costo_operativo',
        'peso',
    ];

    protected $casts = [
        'opciones_avanzadas' => 'boolean',
        'attr_numero_serie' => 'boolean',
        'attr_fecha_vencimiento' => 'boolean',
        'attr_lote_produccion' => 'boolean',
        'attr_venta_menudeo' => 'boolean',
        'precio_compra' => 'decimal:2',
        'pvp' => 'decimal:2',
        'pv_docena' => 'decimal:2',
        'pvp_dto' => 'decimal:2',
        'pvc' => 'decimal:2',
        'pvc_dto' => 'decimal:2',
        'costo_operativo' => 'decimal:2',
        'peso' => 'decimal:3',
    ];

    // Relaciones
    public function lineas()
    {
        return $this->hasMany(ProductoLinea::class, 'producto_id');
    }

    public function familia()
    {
        return $this->belongsTo(\App\Models\Familia::class);
    }

    public function subfamilia()
    {
        return $this->belongsTo(\App\Models\Subfamilia::class);
    }

    public function marca()
    {
        return $this->belongsTo(\App\Models\Marca::class);
    }

    public function unidadMedida()
    {
        return $this->belongsTo(\App\Models\UnidadMedida::class, 'unidad_medida_id');
    }
}