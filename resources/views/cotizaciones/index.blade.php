@extends('layout.app')

@section('title', 'Cotizaciones')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Cotizaciones</h1>
            <small class="text-muted">Gestión de cotizaciones de ventas</small>
        </div>
        <a href="{{ route('cotizaciones.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Cotización
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('cotizaciones.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="aprobada" {{ request('estado') === 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                        <option value="rechazada" {{ request('estado') === 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                        <option value="vencida" {{ request('estado') === 'vencida' ? 'selected' : '' }}>Vencida</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cliente</label>
                    <input type="text" name="cliente" class="form-control" placeholder="Buscar cliente..." value="{{ request('cliente') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de cotizaciones -->
    <div class="card">
        <div class="card-body">
            @if($cotizaciones->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Vigencia</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cotizaciones as $cotizacion)
                                <tr>
                                    <td>
                                        <a href="{{ route('cotizaciones.pdfCotizacion', $cotizacion->id) }}" 
                                           class="text-decoration-none fw-bold">
                                            {{ $cotizacion->numero }}
                                        </a>
                                    </td>
                                    <td>{{ $cotizacion->cliente->nombre ?? 'Sin cliente' }}</td>
                                    <td>{{ $cotizacion->fecha->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="text-{{ $cotizacion->vigencia < now() ? 'danger' : 'success' }}">
                                            {{ $cotizacion->vigencia->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td class="fw-bold">S/ {{ number_format($cotizacion->total, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $cotizacion->color_estado }}">
                                            {{ $cotizacion->texto_estado }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('cotizaciones.show', $cotizacion->id) }}" 
                                               class="btn btn-outline-primary" title="Ver">
                                                <i class='bx bx-show'></i> 
                                            </a>
                                            @if($cotizacion->estado === 'pendiente')
                                                <a href="{{ route('cotizaciones.edit', $cotizacion->id) }}" 
                                                   class="btn btn-outline-secondary" title="Editar">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                            @endif
                                            @if($cotizacion->estado === 'aprobada')
                                                <button type="button" class="btn btn-outline-success" 
                                                        onclick="convertirAVenta({{ $cotizacion->id }})" title="Convertir a Venta">
                                                    <i class="bx bx-shopping-cart"></i>
                                                </button>
                                            @endif
                                            @if($cotizacion->estado === 'pendiente')
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-outline-warning dropdown-toggle" 
                                                            data-bs-toggle="dropdown" title="Cambiar estado">
                                                        <i class="bx bx-transfer"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" 
                                                               onclick="cambiarEstado({{ $cotizacion->id }}, 'aprobada')">Aprobar</a></li>
                                                        <li><a class="dropdown-item" href="#" 
                                                               onclick="cambiarEstado({{ $cotizacion->id }}, 'rechazada')">Rechazar</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#" 
                                                               onclick="eliminarCotizacion({{ $cotizacion->id }})">Eliminar</a></li>
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $cotizaciones->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay cotizaciones disponibles</h5>
                    <p class="text-muted">Comienza creando tu primera cotización.</p>
                    <a href="{{ route('cotizaciones.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nueva Cotización
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@include('cotizaciones.partials.modals')
@endsection

@push('scripts')
<script>
    function cambiarEstado(id, estado) {
        const estados = {
            'aprobada': 'aprobar',
            'rechazada': 'rechazar'
        };
        
        const accion = estados[estado];
        
        if (confirm(`¿Estás seguro de que quieres ${accion} esta cotización?`)) {
            fetch(`/cotizaciones/${id}/cambiar-estado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ estado: estado })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error al cambiar el estado de la cotización');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cambiar el estado de la cotización');
            });
        }
    }

    function convertirAVenta(id) {
        if (confirm('¿Deseas convertir esta cotización en una venta?')) {
            fetch(`/cotizaciones/${id}/convertir-venta`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Error al convertir la cotización');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al convertir la cotización');
            });
        }
    }

    function eliminarCotizacion(id) {
        if (confirm('¿Estás seguro de que quieres eliminar esta cotización? Esta acción no se puede deshacer.')) {
            fetch(`/cotizaciones/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error al eliminar la cotización');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar la cotización');
            });
        }
    }
</script>
@endpush