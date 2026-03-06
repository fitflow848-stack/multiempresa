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

                            <form id="product-search-form" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">
                                        <i class="fas fa-barcode mr-1"></i>
                                        Código Barras
                                    </label>
                                    <input type="text" name="cb" id="search-cb"
                                        class="form-control form-control-sm" placeholder="Buscar por CB">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">
                                        <i class="fas fa-hashtag mr-1"></i>
                                        Referencia
                                    </label>
                                    <input type="text" name="ref" id="search-ref"
                                        class="form-control form-control-sm" placeholder="Código ref.">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bx bx-search mr-1"></i>
                                        Nombre del Producto
                                    </label>
                                    <div class="input-group">
                                        <input type="text" name="q" id="search-q"
                                            class="form-control form-control-sm" placeholder="Buscar por nombre, marca o modelo">
                                        <button class="btn btn-sm btn-success" id="btn-open-quick-create"
                                            type="button" title="Crear producto rápido">
                                            <i class='bx  bx-plus'></i> 
                                        </button>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="d-flex flex-wrap gap-3 align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="filter-stock-min"
                                                name="stock_min">
                                            <label class="form-check-label" for="filter-stock-min">
                                                <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
                                                Stock Mínimo
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="filter-por-proveedor"
                                                name="por_proveedor">
                                            <label class="form-check-label" for="filter-por-proveedor">
                                                <i class="fas fa-truck text-info mr-1"></i>
                                                Por Proveedor
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="filter-obsoletos"
                                                name="obsoletos">
                                            <label class="form-check-label" for="filter-obsoletos">
                                                <i class="fas fa-archive text-secondary mr-1"></i>
                                                Obsoletos
                                            </label>
                                        </div>
                                        
                                        <div class="ms-auto">
                                            <button id="btn-product-search" type="submit"
                                                class="btn btn-primary btn-sm">
                                                <i class="bx bx-search mr-1"></i>
                                                Buscar
                                            </button>
                                            <button id="btn-product-clear" type="button"
                                                class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-eraser mr-1"></i>
                                                Limpiar
                                            </button>
                                        </div>
                                    </div>
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