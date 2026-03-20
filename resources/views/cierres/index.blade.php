@extends('layout.app')

@section('title', 'Cierres de Caja')

@section('content')
    <div class="container-fluid">
        @foreach(['error','success','info','warning'] as $msgType)
            @if(session($msgType))
                <div class="alert alert-{{ $msgType === 'error' ? 'danger' : $msgType }} alert-dismissible fade show" role="alert">
                    <i class="fas fa-{{ $msgType === 'error' ? 'exclamation-circle' : ($msgType === 'success' ? 'check-circle' : 'info-circle') }} me-2"></i>
                    {{ session($msgType) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endforeach

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ $isTesoreria ? 'Arqueos de Tesorería' : 'Cierres de Caja' }}</h1>
                <small class="text-muted">{{ $isTesoreria ? 'Registro y control de bóvedas generales' : 'Registro y control de cierres de caja' }}</small>
            </div>
            <div class="d-flex gap-2">
                @if (!$isTesoreria)
                <a href="{{ route('pos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-cash-register me-2"></i>Ir a TPV
                </a>
                @endif
                @if (!$openCaja)
                    <a href="{{ route('cierre-caja.create', ['tipo' => $isTesoreria ? 'tesoreria' : 'caja']) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>{{ $isTesoreria ? 'Abrir Bóveda' : 'Abrir Caja' }}
                    </a>
                @elseif($isTesoreria)
                    <a href="{{ route('cierre-caja.show', $openCaja->id) }}" class="btn btn-success">
                        <i class="fas fa-vault me-2"></i> Ver Bóveda Activa
                        <span class="badge bg-white text-success ms-1">ABIERTA</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('cierre-caja.index') }}" class="row g-3">
                    @if(auth()->user()->hasAnyRole(['super_admin', 'admin_empresa', 'supervisor']))
                        <div class="col-md-2">
                            <label class="form-label">Usuario</label>
                            <select name="user_id" class="form-select">
                                <option value="">Todos</option>
                                @foreach (App\Models\User::where('company_id', auth()->user()->company_id)->orderBy('name')->get() as $u)
                                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Caja (Contexto Actual)</label>
                            <select name="caja_id" class="form-select">
                                <option value="">Todas</option>
                                @foreach (auth()->user()->cajasDisponibles()->filter(function($c) use ($isTesoreria) { return (bool)$c->is_boveda === $isTesoreria; }) as $c)
                                    <option value="{{ $c->id }}" {{ $filtroCajaId == $c->id ? 'selected' : '' }}>
                                        {{ $c->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="tipo" value="{{ $isTesoreria ? 'tesoreria' : 'caja' }}">
                        </div>
                    @endif

                    <div class="col-md-3">
                        <label class="form-label">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @if ($cierres->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Caja</th>
                                    <th>F. Apertura</th>
                                    <th>F. Cierre</th>
                                    <th>Apertura (S/)</th>
                                    <th>Cierre (S/)</th>
                                    <th>Ingresos</th>
                                    <th>Egresos</th>
                                    <th>Observaciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cierres as $cierre)
                                    <tr>
                                        <td class="fw-bold">#{{ $cierre->id }}</td>
                                        <td>{{ optional($cierre->user)->name }}</td>
                                        <td>
                                            <span class="badge bg-label-info text-info">
                                                <i class="bx bx-box me-1"></i>{{ optional($cierre->caja)->nombre ?? '-' }}
                                            </span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($cierre->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($cierre->fecha_cierre)
                                                {{ \Carbon\Carbon::parse($cierre->fecha_cierre)->format('d/m/Y H:i') }}
                                            @else
                                                <span class="badge bg-label-success">En curso</span>
                                            @endif
                                        </td>
                                        <td>S/ {{ number_format($cierre->monto_apertura, 2) }}</td>
                                        {{-- Si está abierta mostramos el teórico acumulado; si está cerrada el monto real --}}
                                        <td class="fw-bold">
                                            @if($cierre->fecha_cierre)
                                                S/ {{ number_format($cierre->monto_cierre, 2) }}
                                            @else
                                                <span class="text-warning" title="Teórico (caja abierta)">
                                                    ~S/ {{ number_format($cierre->teorico_acumulado ?? $cierre->monto_apertura, 2) }}
                                                </span>
                                            @endif
                                        </td>
                                        {{-- Ingresos: ventas en efectivo + aportaciones --}}
                                        <td>S/ {{ number_format(($cierre->ingresos ?? 0) + ($cierre->aportaciones ?? 0), 2) }}</td>
                                        {{-- Egresos: gastos + sustracciones/retiros --}}
                                        <td>S/ {{ number_format(($cierre->egresos ?? 0) + ($cierre->sustracciones ?? 0), 2) }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($cierre->observaciones, 60) }}</td>
                                        <td>
                                            <div style="display:flex; gap:8px; align-items:center;">
                                                @if ($cierre->fecha_cierre === null)
                                                    <span class="badge bg-success">ABIERTA</span>
                                                @else
                                                    <span class="badge bg-secondary">CERRADA</span>
                                                @endif
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('cierre-caja.show', $cierre->id) }}"
                                                        class="btn btn-outline-primary" title="Ver"><i class="bx bx-show"></i></a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $cierres->withQueryString()->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay cierres registrados</h5>
                        <p class="text-muted">Registra el primer cierre de caja.</p>
                        <a href="{{ route('cierre-caja.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nuevo Cierre
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection