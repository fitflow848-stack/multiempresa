<div class="modal fade" id="proveedorModal" tabindex="-1" aria-labelledby="proveedorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="proveedor-form" method="POST" action="{{ route('proveedores.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="proveedorModalLabel">Alta proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <label for="ruc" class="form-label small text-muted">Número fiscal (RUC)</label>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" id="ruc" name="ruc" maxlength="11"
                            placeholder="Ingrese RUC" />
                        <button type="button" class="btn btn-outline-secondary" id="btn-ruc"
                            title="Buscar RUC">🔍</button>
                    </div>
                    <div id="ruc-status" class="small text-muted mb-3"></div>

                    <div class="mb-3">
                        <label for="nombre_comercial" class="form-label small text-muted">Nombre comercial</label>
                        <input id="nombre_comercial" name="nombre_comercial" type="text"
                            class="form-control form-control-sm" />
                    </div>

                    <div class="mb-3">
                        <label for="nombre_legal" class="form-label small text-muted">Nombre legal</label>
                        <input id="nombre_legal" name="nombre_legal" type="text"
                            class="form-control form-control-sm" />
                    </div>

                    <div class="mb-3">
                        <label for="direccion" class="form-label small text-muted">Dirección</label>
                        <textarea id="direccion" name="direccion" rows="3" class="form-control form-control-sm"></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="localidad" class="form-label small text-muted">Localidad</label>
                            <input id="localidad" name="localidad" type="text"
                                class="form-control form-control-sm" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="codigo_postal" class="form-label small text-muted">Código postal</label>
                            <input id="codigo_postal" name="codigo_postal" type="text"
                                class="form-control form-control-sm" />
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label small text-muted">Ubigeo</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm">+</button>
                            <input name="ubigeo" id="ubigeo" type="text" class="form-control form-control-sm" />
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label small text-muted">Email</label>
                            <input id="email" name="email" type="email" class="form-control form-control-sm" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="telefono" class="form-label small text-muted">Teléfono</label>
                            <input id="telefono" name="telefono" type="text" class="form-control form-control-sm" />
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-proveedor">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
