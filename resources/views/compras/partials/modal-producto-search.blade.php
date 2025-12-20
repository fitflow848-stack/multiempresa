<div class="modal fade" id="productSearchModal" tabindex="-1" aria-labelledby="productSearchModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productSearchModalLabel">Productos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">

                        <!-- Buscador -->
                        <div class="mb-3 pb-2 border-bottom">
                            <h6 class="text-primary">Buscar</h6>

                            <form id="product-search-form" class="row g-2 align-items-center">
                                <div class="col-auto">
                                    <label class="form-label small-muted mb-1">CB</label>
                                    <input type="text" name="cb" id="search-cb"
                                        class="form-control form-control-sm" placeholder="Código Barras">
                                </div>

                                <div class="col-auto">
                                    <label class="form-label small-muted mb-1">Ref.</label>
                                    <input type="text" name="ref" id="search-ref"
                                        class="form-control form-control-sm" placeholder="código referencia">
                                </div>

                                <div class="col-auto" style="min-width:320px; flex:1;">
                                    <label class="form-label small-muted mb-1">Nombre</label>
                                    <div class="input-group">
                                        <input type="text" name="q" id="search-q"
                                            class="form-control form-control-sm" placeholder="nombre | marca ó modelo">
                                        <button class="btn btn-sm btn-outline-secondary" id="btn-open-quick-create"
                                            type="button" title="Alta rápida">+</button>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-check-inline ms-1">
                                        <input class="form-check-input" type="checkbox" id="filter-stock-min"
                                            name="stock_min">
                                        <label class="form-check-label small" for="filter-stock-min">Stock Mínimo</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="filter-por-proveedor"
                                            name="por_proveedor">
                                        <label class="form-check-label small" for="filter-por-proveedor">Por
                                            Proveedor</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="filter-obsoletos"
                                            name="obsoletos">
                                        <label class="form-check-label small" for="filter-obsoletos">Obsoletos</label>
                                    </div>

                                    <button id="btn-product-search" type="submit"
                                        class="btn btn-sm btn-primary ms-3">Buscar</button>
                                    <button id="btn-product-clear" type="button"
                                        class="btn btn-sm btn-outline-secondary ms-1">Limpiar</button>
                                </div>
                            </form>
                        </div>

                        <!-- Resultados -->
                        <div id="product-search-results-wrapper" class="mt-3">
                            <h6 id="product-search-count" class="text-info">Resultado: 0 productos</h6>

                            <div id="product-search-results" style="max-height:480px; overflow:auto;"
                                class="list-group list-group-flush">
                                <!-- filas de resultados se insertan aquí -->
                                <div class="list-group-item text-muted small">Realiza una búsqueda para ver resultados.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <small class="text-muted me-auto">Haz click en "Agregar" para insertar el producto en la compra.
                            "+"
                            abre alta rápida.</small>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>