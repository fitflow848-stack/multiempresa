<div id="context-menu"
    style="position: absolute; background: white; border: 1px solid #ccc; box-shadow: 2px 2px 10px rgba(0,0,0,0.2); display: none; z-index: 1000; min-width: 200px; border-radius: 4px; font-family: Arial, sans-serif; max-height: 70vh; overflow-y: auto;">
    <div class="context-menu-item" onclick="abrirModalCantidad()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #2E7D32;">🔢</span>
        <span>Vender · ¿Cuántas?</span>
    </div>
    <div class="context-menu-item" onclick="venderPrecioPublico()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #28a745;">🛒</span>
        <span>Vender a Precio Público</span>
    </div>
    <div class="context-menu-item" onclick="venderPrecioCorp()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #2196F3;">🛒</span>
        <span>Vender a Precio Corp</span>
    </div>
    <div class="context-menu-item" onclick="venderLoteVencimiento()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #4CAF50;">🛒</span>
        <span>Vender a Lote Vencimiento</span>
        <span style="margin-left: auto;">▶</span>
    </div>
    <div class="context-menu-item" onclick="mostrarFichaTecnica()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #FF9800;">📋</span>
        <span>Ficha Técnica</span>
    </div>
    <div class="context-menu-item" onclick="mostrarFichaExistencias()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #9C27B0;">📊</span>
        <span>Ficha de Existencias</span>
    </div>
    <div class="context-menu-item" onclick="mostrarUbicacionProducto()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #607D8B;">📍</span>
        <span>Ubicación del Producto</span>
    </div>
    <div class="context-menu-item" onclick="anotarNuevoProducto()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #4CAF50;">✏️</span>
        <span>Anotar Nuevo Producto</span>
    </div>
    <div class="context-menu-item"
        onclick="typeof Swal !== 'undefined' ? cancelarVentaConSweetAlert() : cancelarVenta()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px;">
        <span style="color: #F44336;">❌</span>
        <span>Cancelar Venta</span>
    </div>
    <div class="context-menu-item" onclick="abrirModalRomperDocena()"
        style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 8px; background: #fff5f5;">
        <span style="color: #d63384;">⚒️</span>
        <span style="font-weight: 700; color: #d63384;">Conversión Docena/Saco</span>
    </div>
    <div class="context-menu-item" onclick="limpiarLista()"
        style="padding: 8px 12px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
        <span style="color: #FF5722;">🗑️</span>
        <span>Limpiar Lista</span>
    </div>
</div>
@include('pos.partials.js.cantidad-venta')
