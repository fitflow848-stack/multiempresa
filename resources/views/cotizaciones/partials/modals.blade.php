<!-- Modal de cliente -->
<div class="modal fade" id="clienteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="modal-cliente-search" class="form-control" 
                           placeholder="Buscar por nombre, documento o teléfono...">
                </div>
                <div id="modal-clientes-list" class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <!-- Lista de clientes se carga aquí -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de cantidad/precio -->
<div class="modal fade" id="cantidadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="producto-info" class="mb-3">
                    <!-- Info del producto -->
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Cantidad *</label>
                        <input type="number" id="cantidad-input" class="form-control" min="0.001" step="0.001" value="1">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Precio unitario *</label>
                        <div class="input-group">
                            <span class="input-group-text">S/</span>
                            <input type="number" id="precio-input" class="form-control" min="0" step="0.01">
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Descuento</label>
                        <div class="input-group">
                            <span class="input-group-text">S/</span>
                            <input type="number" id="descuento-input" class="form-control" min="0" step="0.01" value="0">
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Subtotal</label>
                        <div class="input-group">
                            <span class="input-group-text">S/</span>
                            <input type="text" id="subtotal-input" class="form-control" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Lote</label>
                        <input type="text" id="lote-input" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Fecha vencimiento</label>
                        <input type="date" id="fecha-vencimiento-input" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="add-product-btn">Agregar</button>
            </div>
        </div>
    </div>
</div>