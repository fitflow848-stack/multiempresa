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
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0 fw-bold">Historial de Movimientos</h5>
                    </div>
                    <form method="GET" action="{{ route('bancos.show', $banco->id) }}" class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label small mb-0">Desde</label>
                            <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ $fecha_desde ?? '' }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label small mb-0">Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ $fecha_hasta ?? '' }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label small mb-0">Sucursal</label>
                            <select name="sucursal_id" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->id }}" {{ ($sucursal_id ?? '') == $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="bx bx-filter-alt"></i> Filtrar</button>
                        </div>
                        @if(($fecha_desde ?? null) || ($fecha_hasta ?? null) || ($sucursal_id ?? null))
                        <div class="col-auto">
                            <a href="{{ route('bancos.show', $banco->id) }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                        </div>
                        @endif
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Fecha / Hora</th>
                                <th>Sucursal</th>
                                <th>Concepto</th>
                                <th>Referencia</th>
                                <th class="text-end">Monto</th>
                                <th class="text-end">Saldo</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $saldoAcumulado = $banco->saldo_actual; @endphp
                            @forelse($movimientos as $mov)
                            <tr>
                                <td class="small">
                                    {{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}
                                    <br><span class="text-muted">{{ $mov->created_at ? $mov->created_at->format('H:i') : '' }}</span>
                                </td>
                                <td class="small">{{ $mov->sucursal ? $mov->sucursal->nombre : ($mov->sucursal_id ? 'ID:'.$mov->sucursal_id : '-') }}</td>
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
                                <td class="text-end fw-bold">
                                    {{ number_format($saldoAcumulado, 2) }}
                                    @php
                                        $saldoAcumulado -= ($mov->tipo === 'ingreso' ? $mov->monto : -$mov->monto);
                                    @endphp
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-warning p-1"
                                        onclick="editarMovimiento({{ $mov->id }}, '{{ $mov->tipo }}', {{ $mov->monto }}, '{{ addslashes($mov->concepto) }}', '{{ $mov->referencia }}', '{{ $mov->fecha }}')"
                                        title="Editar">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                    <form action="{{ route('bancos.movimiento.destroy', [$banco->id, $mov->id]) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('¿Eliminar este movimiento? Se revertirá el saldo.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Eliminar">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
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
                            <select name="tipo" id="tipoMovimiento" class="form-select" required>
                                <option value="ingreso">Ingreso (+)</option>
                                <option value="egreso">Egreso (-)</option>
                                <option value="pase_caja">Pase a Caja (Banco → Caja)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 d-none" id="seccionSucursalPase">
                            <label class="form-label fw-bold">Sucursal destino (Caja)</label>
                            <select name="sucursal_destino_id" id="sucursalDestinoSelect" class="form-select">
                                @php
                                    $sucursalesPase = \App\Models\Sucursal::where('company_id', auth()->user()->company_id)->activas()->get();
                                @endphp
                                @foreach($sucursalesPase as $suc)
                                    <option value="{{ $suc->id }}" {{ auth()->user()->branch_id == $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Se sumará como ingreso a la caja abierta de esta sucursal.</small>
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

<!-- Modal Editar Movimiento -->
<div class="modal fade" id="modalEditarMovimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formEditarMovimiento" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar Movimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de Operación</label>
                            <select name="tipo" id="editMovTipo" class="form-select" required>
                                <option value="ingreso">Ingreso (+)</option>
                                <option value="egreso">Egreso (-)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha</label>
                            <input type="date" name="fecha" id="editMovFecha" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Monto ({{ $banco->moneda }})</label>
                            <input type="number" step="0.01" name="monto" id="editMovMonto" class="form-control form-control-lg fw-bold" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Concepto / Motivo</label>
                            <input type="text" name="concepto" id="editMovConcepto" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Referencia (Opcional)</label>
                            <input type="text" name="referencia" id="editMovReferencia" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Movimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarMovimiento(id, tipo, monto, concepto, referencia, fecha) {
    document.getElementById('formEditarMovimiento').action = "{{ url('bancos') }}/{{ $banco->id }}/movimiento/" + id;
    document.getElementById('editMovTipo').value = tipo;
    document.getElementById('editMovMonto').value = monto;
    document.getElementById('editMovConcepto').value = concepto;
    document.getElementById('editMovReferencia').value = referencia || '';
    document.getElementById('editMovFecha').value = fecha;
    new bootstrap.Modal(document.getElementById('modalEditarMovimiento')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('tipoMovimiento');
    const seccionSucursal = document.getElementById('seccionSucursalPase');
    
    if (tipoSelect) {
        tipoSelect.addEventListener('change', function() {
            seccionSucursal.classList.toggle('d-none', this.value !== 'pase_caja');
        });
    }
});
</script>
@endsection
