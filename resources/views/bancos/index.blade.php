@extends('layout.app')

@section('title', 'Gestión de Bancos y Cuentas')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Finanzas /</span> Bancos y Cuentas
        </h4>
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaCuenta">
            <i class="bx bx-plus me-1"></i> Nueva Cuenta
        </button>
    </div>

    <div class="row">
        @forelse($cuentas as $cuenta)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-label-primary d-flex justify-content-between align-items-center border-bottom-0 pb-0">
                    <span class="badge bg-white text-primary fw-bold">{{ $cuenta->moneda }}</span>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('bancos.show', $cuenta->id) }}"><i class="bx bx-show me-1"></i> Ver Movimientos</a>
                            <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalEditCuenta{{ $cuenta->id }}"><i class="bx bx-edit-alt me-1"></i> Editar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bxs-bank fs-4"></i></span>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">{{ $cuenta->banco_nombre }}</h5>
                            <small class="text-muted">{{ $cuenta->tipo_cuenta ?? 'Cuenta Bancaria' }}</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase fw-bold">Número de Cuenta</label>
                        <div class="d-flex align-items-center">
                            <span class="fw-bold">{{ $cuenta->numero_cuenta }}</span>
                            <button class="btn btn-xs btn-outline-secondary border-0 ms-2" onclick="copyToClipboard('{{ $cuenta->numero_cuenta }}')" title="Copiar">
                                <i class="bx bx-copy"></i>
                            </button>
                        </div>
                    </div>

                    @if($cuenta->cci)
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase fw-bold">CCI</label>
                        <div class="d-flex align-items-center">
                            <span class="small">{{ $cuenta->cci }}</span>
                        </div>
                    </div>
                    @endif

                    <div class="bg-light p-3 rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Saldo Disponible</span>
                            <h4 class="mb-0 fw-bold text-{{ $cuenta->saldo_actual >= 0 ? 'success' : 'danger' }}">
                                {{ $cuenta->moneda }} {{ number_format($cuenta->saldo_actual, 2) }}
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top-0 pt-0">
                    <a href="{{ route('bancos.show', $cuenta->id) }}" class="btn btn-sm btn-outline-primary w-100">Ver Detalles</a>
                </div>
            </div>
        </div>

        <!-- Modal Editar Cuenta -->
        <div class="modal fade" id="modalEditCuenta{{ $cuenta->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('bancos.update', $cuenta->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Editar Cuenta: {{ $cuenta->banco_nombre }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Nombre del Banco</label>
                                    <input type="text" name="banco_nombre" class="form-control" value="{{ $cuenta->banco_nombre }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de Cuenta</label>
                                    <input type="text" name="tipo_cuenta" class="form-control" value="{{ $cuenta->tipo_cuenta }}" placeholder="Ej: Ahorros">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Número de Cuenta</label>
                                    <input type="text" name="numero_cuenta" class="form-control" value="{{ $cuenta->numero_cuenta }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">CCI (Interbancario)</label>
                                    <input type="text" name="cci" class="form-control" value="{{ $cuenta->cci }}">
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $cuenta->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label">Cuenta Activa</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="mb-3">
                <i class="bx bx-info-circle fs-1 text-muted"></i>
            </div>
            <h5>No hay cuentas bancarias registradas</h5>
            <p class="text-muted">Comience registrando su primera cuenta para gestionar sus transacciones digitales.</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaCuenta">Registrar Cuenta</button>
        </div>
        @endforelse
    </div>
</div>

<!-- Modal Nueva Cuenta -->
<div class="modal fade" id="modalNuevaCuenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('bancos.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Nueva Cuenta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Banco / Entidad Financiera</label>
                            <input type="text" name="banco_nombre" class="form-control" placeholder="Ej: BCP, BBVA, Interbank..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Moneda</label>
                            <select name="moneda" class="form-select">
                                <option value="PEN">Soles (S/)</option>
                                <option value="USD">Dólares ($)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de Cuenta</label>
                            <input type="text" name="tipo_cuenta" class="form-control" placeholder="Ahorros, Corriente...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Número de Cuenta</label>
                            <input type="text" name="numero_cuenta" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">CCI</label>
                            <input type="text" name="cci" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-primary">Saldo Inicial</label>
                            <div class="input-group">
                                <span class="input-group-text">S/</span>
                                <input type="number" step="0.01" name="saldo_inicial" class="form-control fw-bold" value="0.00" required>
                            </div>
                            <small class="text-muted">Monto con el que inicia la gestión en el sistema.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar Cuenta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Copiado',
            text: 'Número de cuenta copiado al portapapeles',
            timer: 1500,
            showConfirmButton: false
        });
    });
}
</script>
@endsection
