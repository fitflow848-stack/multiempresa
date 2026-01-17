@extends('layout.app')

@section('title', 'Cierres de Caja')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Cierres de Caja</h1>
                <small class="text-muted">Registro y control de cierres de caja</small>
            </div>
            @if (!$openCaja)
                <a href="{{ route('cierre-caja.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Abrir Caja
                </a>
            @endif
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('cierre-caja.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Usuario</label>
                        <select name="user_id" class="form-select">
                            <option value="">Todos</option>
                            @foreach (App\Models\User::orderBy('name')->get() as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

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
                                    <th>Fecha</th>
                                    <th>Apertura</th>
                                    <th>Cierre</th>
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
                                        <td>{{ optional($cierre->fecha_cierre) ? \Carbon\Carbon::parse($cierre->fecha_cierre)->format('d/m/Y H:i') : '-' }}
                                        </td>
                                        <td>S/ {{ number_format($cierre->monto_apertura, 2) }}</td>
                                        <td class="fw-bold">S/ {{ number_format($cierre->monto_cierre, 2) }}</td>
                                        <td>S/ {{ number_format($cierre->ingresos, 2) }}</td>
                                        <td>S/ {{ number_format($cierre->egresos, 2) }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($cierre->observaciones, 60) }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('cierre-caja.show', $cierre->id) }}"
                                                    class="btn btn-outline-primary" title="Ver"><i
                                                        class="bx bx-show"></i></a>
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
