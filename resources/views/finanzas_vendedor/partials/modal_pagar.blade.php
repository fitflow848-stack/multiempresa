{{-- Modal Pagar para cada operación --}}
<div class="modal fade" id="modalPagar{{ $op->id }}" tabindex="-1" aria-hidden="true" style="font-size: 0.9rem;">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Pago: {{ $op->empresa_persona }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pasivos.pagar', $op->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label mb-0 small fw-bold">Monto a Pagar (Saldo: S/ {{ number_format($op->saldo, 2) }})</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">S/</span>
                                <input type="number" step="0.01" name="monto" class="form-control" value="{{ $op->saldo }}" max="{{ $op->saldo }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-0 small fw-bold">Fecha Pago</label>
                            <input type="date" name="fecha_pago" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-0 small fw-bold">Método de Pago</label>
                            <select name="metodo_pago" class="form-select form-select-sm" required>
                                <option value="Efectivo">Efectivo (Caja)</option>
                                <option value="Transferencia">Transferencia (Banco)</option>
                                <option value="Yape/Plin">Yape/Plin</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-0 small fw-bold">Observaciones</label>
                            <textarea name="observaciones" class="form-control form-control-sm" rows="2" placeholder="Opcional..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary">Confirmar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>
