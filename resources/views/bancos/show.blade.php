@extends('layout.app')

@section('title', 'Detalle de Cuenta: ' . $banco->banco_nombre)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Bancos /</span> {{ $banco->banco_nombre }}
        </h4>
        <div>
            <a href="{{ route('bancos.index') }}" class="btn btn-outline-secondary me-2">
                <i class="bx bx-chevron-left"></i> Volver
            </a>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalMovimiento">
                <i class="bx bx-transfer me-1"></i> Nuevo Movimiento
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="avatar avatar-lg me-3">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bxs-bank fs-2"></i></span>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $banco->banco_nombre }}</h5>
                            <span class="badge bg-label-success">{{ $banco->tipo_cuenta }}</span>
                        </div>
                    </div>

                    <div class="info-container">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <span class="fw-bold text-muted small text-uppercase d-block">Número de Cuenta</span>
                                <span class="fw-bold fs-5">{{ $banco->numero_cuenta }}</span>
                            </li>
                            @if($banco->cci)
                            <li class="mb-3">
                                <span class="fw-bold text-muted small text-uppercase d-block">CCI (Interbancario)</span>
                                <span>{{ $banco->cci }}</span>
                            </li>
                            @endif
                            <li class="mb-3">
                                <span class="fw-bold text-muted small text-uppercase d-block">Moneda</span>
                                <span class="badge bg-label-primary">{{ $banco->moneda }}</span>
                            </li>
                        </ul>
                        
                        <div class="bg-primary text-white p-4 rounded shadow-sm mt-4">
                            <span class="small text-uppercase opacity-75 d-block mb-1">Saldo Actual</span>
                            <h2 class="mb-0 text-white fw-bold">{{ $banco->moneda }} {{ number_format($banco->saldo_actual, 2) }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Historial de Movimientos</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Fecha / Hora</th>
                                <th>Concepto</th>
                                <th>Referencia</th>
                                <th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movimientos as $mov)
                            <tr>
                                <td class="small">
                                    {{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}
                                    <br><span class="text-muted">{{ $mov->created_at ? $mov->created_at->format('H:i') : '' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="badge rounded-pill bg-label-{{ $mov->tipo === 'ingreso' ? 'success' : 'danger' }} me-2 p-1">
                                            <i class="bx bx-{{ $mov->tipo === 'ingreso' ? 'plus' : 'minus' }}"></i>
                                        </div>
                                        <span class="fw-medium">{{ $mov->concepto }}</span>
                                    </div>
                                </td>
                                <td><span class="text-muted small">{{ $mov->referencia ?? '-' }}</span></td>
                                <td class="text-end fw-bold text-{{ $mov->tipo === 'ingreso' ? 'success' : 'danger' }}">
                                    {{ $mov->tipo === 'ingreso' ? '+' : '-' }} {{ number_format($mov->monto, 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    No hay movimientos registrados en esta cuenta.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    {{ $movimientos->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Movimiento -->
<div class="modal fade" id="modalMovimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('bancos.movimiento.store', $banco->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Movimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de Operación</label>
                            <select name="tipo" class="form-select" required>
                                <option value="ingreso">Ingreso (+)</option>
                                <option value="egreso">Egreso (-)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Monto ({{ $banco->moneda }})</label>
                            <input type="number" step="0.01" name="monto" class="form-control form-control-lg fw-bold" placeholder="0.00" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Concepto / Motivo</label>
                            <input type="text" name="concepto" class="form-control" placeholder="Ej: Pago de cliente, Transferencia..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Referencia (Opcional)</label>
                            <input type="text" name="referencia" class="form-control" placeholder="Nro de operación, ticket, etc.">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar Movimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
