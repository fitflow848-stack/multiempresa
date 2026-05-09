<!-- Modal Ventas Guardadas -->
<div class="modal fade" id="modalVentasGuardadas" tabindex="-1" aria-labelledby="modalVentasGuardadasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-0 py-3">
                <h5 class="modal-title d-flex align-items-center fw-bold text-dark" id="modalVentasGuardadasLabel">
                    <i class='bx bx-time-five me-2 text-primary fs-4'></i> Ventas en Espera / Guardadas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive" style="max-height: 450px;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-soft-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4 py-3">Código</th>
                                <th class="py-3">Fecha y Hora</th>
                                <th class="py-3">Cliente</th>
                                <th class="py-3 text-end">Total</th>
                                <th class="pe-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="lista-ventas-guardadas">
                            <!-- Se cargará por JS -->
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                    Cargando ventas guardadas...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
    #modalVentasGuardadas .bg-soft-light {
        background-color: #f8f9fa;
    }
    #modalVentasGuardadas .table-hover tbody tr:hover {
        background-color: #f1f5f9;
        cursor: pointer;
    }
</style>
