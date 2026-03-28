<div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productDetailModalLabel">Detalle del Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="detail-product-id">

                <div class="mb-2">
                    <strong id="detail-codigo" class="text-muted"></strong>
                    <div id="detail-nombre" class="fw-semibold"></div>
                </div>

                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label">Cantidad</label>
                        <input id="detail-cantidad" type="number" min="1" step="1" class="form-control form-control-sm" value="1">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Costo</label>
                        <input id="detail-costo" type="number" min="0" step="0.01" class="form-control form-control-sm text-end" value="0.00">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Descuento</label>
                        <input id="detail-descuento" type="number" min="0" step="0.01" class="form-control form-control-sm text-end" value="0.00">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Stock Min</label>
                        <input id="detail-stock-min" type="number" min="0" step="1" class="form-control form-control-sm text-center" value="0">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Stock Max</label>
                        <input id="detail-stock-max" type="number" min="0" step="1" class="form-control form-control-sm text-center" value="0">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-primary fw-bold">PVP (Soles)</label>
                        <input id="detail-pvp" type="number" min="0" step="0.01" class="form-control form-control-sm text-end" value="0.00">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-warning fw-bold">PVP Dcto.</label>
                        <input id="detail-pvp-dto" type="number" min="0" step="0.01" class="form-control form-control-sm text-end" value="0.00">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-info fw-bold">PVC (Corp.)</label>
                        <input id="detail-pvc" type="number" min="0" step="0.01" class="form-control form-control-sm text-end" value="0.00">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-primary fw-bold">PVC Dcto.</label>
                        <input id="detail-pvc-dto" type="number" min="0" step="0.01" class="form-control form-control-sm text-end" value="0.00">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-success fw-bold">PV Docena</label>
                        <input id="detail-pv-docena" type="number" min="0" step="0.01" class="form-control form-control-sm text-end" value="0.00">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Lote</label>
                        <input id="detail-lote" type="text" class="form-control form-control-sm" maxlength="50" placeholder="Lote...">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Fecha Venc.</label>
                        <input id="detail-fecha-vencimiento" type="date" class="form-control form-control-sm">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-confirm-add-product">Agregar a la compra</button>
            </div>
        </div>
    </div>
</div>
