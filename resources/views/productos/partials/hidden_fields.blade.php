<input type="hidden" name="laboratorio_id" value="{{ old('laboratorio_id', $producto_data['laboratorio_id'] ?? '') }}">
<input type="hidden" name="familia_id" value="{{ old('familia_id', $producto_data['familia_id'] ?? '') }}">
<input type="hidden" name="subfamilia_id" value="{{ old('subfamilia_id', $producto_data['subfamilia_id'] ?? '') }}">
<input type="hidden" name="marca_id" value="{{ old('marca_id', $producto_data['marca_id'] ?? '') }}">
<input type="hidden" name="unidad_medida_id"
    value="{{ old('unidad_medida_id', $producto_data['unidad_medida_id'] ?? '') }}">
<input type="hidden" name="presentacion_id"
    value="{{ old('presentacion_id', $producto_data['presentacion_id'] ?? '') }}">
<input type="hidden" name="concentracion_id"
    value="{{ old('concentracion_id', $producto_data['concentracion_id'] ?? '') }}">
<input type="hidden" name="tipo_impuesto" value="{{ old('tipo_impuesto', $producto_data['tipo_impuesto'] ?? '') }}">
<input type="hidden" name="condicion_venta"
    value="{{ old('condicion_venta', $producto_data['condicion_venta'] ?? '') }}">
<input type="hidden" name="codigo_personalizado"
    value="{{ old('codigo_personalizado', $producto_data['codigo_personalizado'] ?? '') }}">
<input type="hidden" name="notas" value="{{ old('notas', $producto_data['notas'] ?? '') }}">
<input type="hidden" name="opciones_avanzadas"
    value="{{ old('opciones_avanzadas', $producto_data['opciones_avanzadas'] ?? 0) }}">
<input type="hidden" name="attr_numero_serie"
    value="{{ old('attr_numero_serie', $producto_data['attr_numero_serie'] ?? 0) }}">
<input type="hidden" name="attr_fecha_vencimiento"
    value="{{ old('attr_fecha_vencimiento', $producto_data['attr_fecha_vencimiento'] ?? 0) }}">
<input type="hidden" name="attr_lote_produccion"
    value="{{ old('attr_lote_produccion', $producto_data['attr_lote_produccion'] ?? 0) }}">
<input type="hidden" name="attr_venta_menudeo"
    value="{{ old('attr_venta_menudeo', $producto_data['attr_venta_menudeo'] ?? 0) }}">
<input type="hidden" name="nombre" value="{{ old('nombre', $producto_data['nombre'] ?? '') }}">

<!-- Campos de características como JSON -->
@if (isset($producto_data['propiedades']) && is_array($producto_data['propiedades']))
    @foreach ($producto_data['propiedades'] as $key => $value)
        <input type="hidden" name="propiedades[{{ $key }}]" value="{{ $value }}">
    @endforeach
@endif

@if (isset($producto_data['almacenamiento']) && is_array($producto_data['almacenamiento']))
    @foreach ($producto_data['almacenamiento'] as $key => $value)
        <input type="hidden" name="almacenamiento[{{ $key }}]" value="{{ $value }}">
    @endforeach
@endif

@if (isset($producto_data['seguridad']) && is_array($producto_data['seguridad']))
    @foreach ($producto_data['seguridad'] as $key => $value)
        <input type="hidden" name="seguridad[{{ $key }}]" value="{{ $value }}">
    @endforeach
@endif

@if (isset($producto_data['ficha_tecnica']) && is_array($producto_data['ficha_tecnica']))
    @foreach ($producto_data['ficha_tecnica'] as $key => $value)
        <input type="hidden" name="ficha_tecnica[{{ $key }}]" value="{{ $value }}">
    @endforeach
@endif

<!-- Campos de imágenes -->
<input type="hidden" name="imagen_alt" value="{{ old('imagen_alt', $producto_data['imagen_alt'] ?? '') }}">
<input type="hidden" name="imagen_titulo" value="{{ old('imagen_titulo', $producto_data['imagen_titulo'] ?? '') }}">
<input type="hidden" name="imagen_fuente" value="{{ old('imagen_fuente', $producto_data['imagen_fuente'] ?? '') }}">

<!-- Información de imágenes subidas (no los archivos, solo la info) -->
@if (isset($producto_data['uploaded_images']) && is_array($producto_data['uploaded_images']))
    @foreach ($producto_data['uploaded_images'] as $index => $imageInfo)
        <input type="hidden" name="uploaded_images[{{ $index }}][name]"
            value="{{ $imageInfo['name'] ?? '' }}">
        <input type="hidden" name="uploaded_images[{{ $index }}][path]"
            value="{{ $imageInfo['path'] ?? '' }}">
        <input type="hidden" name="uploaded_images[{{ $index }}][temp_path]"
            value="{{ $imageInfo['temp_path'] ?? '' }}">
    @endforeach
@endif

<!-- Campos temporales de imágenes desde step1 -->
@if (isset($producto_data['imagen_principal_temp']))
    <input type="hidden" name="imagen_principal_temp" value="{{ $producto_data['imagen_principal_temp'] }}">
@endif

@if (isset($producto_data['imagenes_adicionales_temp']) && is_array($producto_data['imagenes_adicionales_temp']))
    @foreach ($producto_data['imagenes_adicionales_temp'] as $index => $filename)
        <input type="hidden" name="imagenes_adicionales_temp[{{ $index }}]" value="{{ $filename }}">
    @endforeach
@endif

<!-- Campo para líneas de producto (disgregados) -->
<input type="hidden" name="product_lines" id="product_lines" value="[]">
