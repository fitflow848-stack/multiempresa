<div class="modal fade" id="modalPresentacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-box me-2"></i>
                    Nueva Presentación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formPresentacion">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" id="nombrePresentacion" class="form-control"
                            placeholder="Ej: TABLETAS, CAPSULAS" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea id="descripcionPresentacion" class="form-control" rows="2" placeholder="Descripción opcional"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarPresentacion">
                    <i class="fas fa-save me-1"></i>
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>
