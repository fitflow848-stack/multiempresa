<div class="modal fade" id="modalConcentracion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-vial me-2"></i>
                    Nueva Concentración
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formConcentracion">
                    <div class="row g-3">
                        <div class="col-8">
                            <label class="form-label">Nombre *</label>
                            <input type="text" id="nombreConcentracion" class="form-control"
                                placeholder="Ej: 500, 250" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Unidad</label>
                            <select id="unidadConcentracion" class="form-select">
                                <option value="">-</option>
                                <option value="MG">MG</option>
                                <option value="ML">ML</option>
                                <option value="KG">KG</option>
                                <option value="GR">GR</option>
                                <option value="LT">LT</option>
                                <option value="UI">UI</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea id="descripcionConcentracion" class="form-control" rows="2" placeholder="Descripción opcional"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarConcentracion">
                    <i class="fas fa-save me-1"></i>
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>
