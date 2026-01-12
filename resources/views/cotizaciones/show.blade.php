@extends('layout.app')

@section('title', 'Cotización ' . $cotizacion->numero)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Cotización {{ $cotizacion->numero }}</h1>
            <small class="text-muted">Detalles de la cotización</small>
        </div>
        <div class="d-flex gap-2">
            @if($cotizacion->estado === 'pendiente')
                <a href="{{ route('cotizaciones.edit', $cotizacion->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit me-2"></i>Editar
                </a>
            @endif
            @if($cotizacion->estado === 'aprobada')
                <button type="button" class="btn btn-success" onclick="convertirAVenta({{ $cotizacion->id }})">
                    <i class="fas fa-shopping-cart me-2"></i>Convertir a Venta
                </button>
            @endif
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Imprimir
            </button>
            <a href="{{ route('cotizaciones.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información principal -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información de la Cotización</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="120"><strong>Número:</strong></td>
                                    <td>{{ $cotizacion->numero }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Fecha:</strong></td>
                                    <td>{{ $cotizacion->fecha->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Vigencia:</strong></td>
                                    <td>
                                        <span class="text-{{ $cotizacion->vigencia < now() ? 'danger' : 'success' }}">
                                            {{ $cotizacion->vigencia->format('d/m/Y') }}
                                            @if($cotizacion->vigencia < now())
                                                <small>(Vencida)</small>
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Estado:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $cotizacion->color_estado }}">
                                            {{ $cotizacion->texto_estado }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="120"><strong>Cliente:</strong></td>
                                    <td>{{ $cotizacion->cliente->nombre }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Documento:</strong></td>
                                    <td>{{ $cotizacion->cliente->tipo_doc }} {{ $cotizacion->cliente->documento }}</td>
                                </tr>
                                @if($cotizacion->cliente->telefono)
                                <tr>
                                    <td><strong>Teléfono:</strong></td>
                                    <td>{{ $cotizacion->cliente->telefono }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Creado por:</strong></td>
                                    <td>{{ $cotizacion->usuario->name }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($cotizacion->observaciones)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <strong>Observaciones:</strong>
                                <p class="mb-0 mt-1">{{ $cotizacion->observaciones }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Productos -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Productos Cotizados</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Descripción</th>
                                    <th width="80">Cant.</th>
                                    <th width="120">P. Unitario</th>
                                    <th width="100">Descuento</th>
                                    <th width="120">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cotizacion->detalles as $detalle)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $detalle->descripcion }}</strong>
                                                @if($detalle->lote)
                                                    <br><small class="text-muted">Lote: {{ $detalle->lote }}</small>
                                                @endif
                                                @if($detalle->fecha_vencimiento)
                                                    <br><small class="text-muted">Venc: {{ $detalle->fecha_vencimiento->format('d/m/Y') }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">{{ number_format($detalle->cantidad, 3) }}</td>
                                        <td class="text-end">S/ {{ number_format($detalle->precio_unitario, 2) }}</td>
                                        <td class="text-end text-danger">
                                            @if($detalle->descuento > 0)
                                                -S/ {{ number_format($detalle->descuento, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold">S/ {{ number_format($detalle->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Resumen -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Resumen</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span class="fw-bold">S/ {{ number_format($cotizacion->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Descuento:</span>
                        <span class="text-danger">-S/ {{ number_format($cotizacion->descuento_total, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>IGV (18%):</span>
                        <span>S/ {{ number_format($cotizacion->igv, 2) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fs-5"><strong>Total:</strong></span>
                        <span class="fs-5 fw-bold text-primary">S/ {{ number_format($cotizacion->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            @if($cotizacion->estado === 'pendiente')
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Acciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success" onclick="cambiarEstado({{ $cotizacion->id }}, 'aprobada')">
                                <i class="fas fa-check me-2"></i>Aprobar Cotización
                            </button>
                            <button type="button" class="btn btn-danger" onclick="cambiarEstado({{ $cotizacion->id }}, 'rechazada')">
                                <i class="fas fa-times me-2"></i>Rechazar Cotización
                            </button>
                            <hr>
                            <button type="button" class="btn btn-outline-danger" onclick="eliminarCotizacion({{ $cotizacion->id }})">
                                <i class="fas fa-trash me-2"></i>Eliminar Cotización
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
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
                    window.location.href = '{{ route("cotizaciones.index") }}';
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

@push('styles')
<style>
    @media print {
        .btn, .card-header, nav, .sidebar {
            display: none !important;
        }
        
        .container-fluid {
            padding: 0 !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .card-body {
            padding: 0 !important;
        }
    }
</style>
@endpush