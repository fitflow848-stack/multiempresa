@extends('layout.app')

@section('title', 'Cotizaciones')

@section('content')
    <style>
        :root {
            --primary-soft: #eef2ff;
            --accent-color: #6366f1;
        }

        .bg-gradient-quote {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 0.65rem 1rem;
        }

        .customer-name {
            font-weight: 600;
            color: #1e293b;
        }

        .quote-number {
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 0.9rem;
            color: #6366f1;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2 class="fw-bold text-dark mb-1">Cotizaciones</h2>
                <p class="text-muted mb-0">Seguimiento de propuestas comerciales y preventas</p>
            </div>
        </div>



        <div class="table-container shadow-sm">
            <div class="row g-3 mb-4 pb-3 border-bottom">
                <form method="GET" action="{{ route('cotizaciones.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Estado de Cotización</label>
                        <select name="estado" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>⏳ Pendiente
                            </option>
                            <option value="aprobada" {{ request('estado') === 'aprobada' ? 'selected' : '' }}>✅ Aprobada
                            </option>
                            <option value="rechazada" {{ request('estado') === 'rechazada' ? 'selected' : '' }}>❌ Rechazada
                            </option>
                            <option value="vencida" {{ request('estado') === 'vencida' ? 'selected' : '' }}>⚠️ Vencida
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Cliente</label>
                        <input type="text" name="cliente" class="form-control" placeholder="Nombre del cliente..."
                            value="{{ request('cliente') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-dark w-100 py-2 fw-bold shadow-sm">
                            <i class="fas fa-filter me-2"></i>Filtrar
                        </button>
                    </div>
                </form>
            </div>
            @if ($cotizaciones->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 py-3">NÚMERO</th>
                                <th class="border-0 py-3">CLIENTE</th>
                                <th class="border-0 py-3">FECHA EMISIÓN</th>
                                <th class="border-0 py-3">VIGENCIA</th>
                                <th class="border-0 py-3 text-end">TOTAL</th>
                                <th class="border-0 py-3 text-center">ESTADO</th>
                                <th class="border-0 py-3 text-center">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cotizaciones as $cotizacion)
                                <tr>
                                    <td>
                                        <a href="{{ route('cotizaciones.pdfCotizacion', $cotizacion->id) }}"
                                            class="quote-number text-decoration-none fw-bold">
                                            #{{ $cotizacion->numero }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="customer-name">{{ $cotizacion->cliente->nombre ?? 'Consumidor Final' }}
                                        </div>
                                        <small class="text-muted">{{ $cotizacion->cliente->documento ?? '' }}</small>
                                    </td>
                                    <td>{{ $cotizacion->fecha->format('d/m/Y') }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $cotizacion->vigencia < now() ? 'bg-danger-subtle text-danger' : 'bg-light text-dark' }} border px-2 py-1">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            {{ $cotizacion->vigencia->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold">
                                        S/ {{ number_format($cotizacion->total, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="status-badge bg-{{ $cotizacion->color_estado }} text-white">
                                            {{ $cotizacion->texto_estado }}
                                        </span>
                                        @if ($cotizacion->ventas->count() > 0)
                                            <div class="mt-1">
                                                <span class="badge bg-info text-white" style="font-size: 0.65rem;">
                                                    <i class="fas fa-check-double me-1"></i>FACTURADO
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            @php
                                                $tieneVenta = $cotizacion->ventas->count() > 0;
                                            @endphp

                                            <a href="{{ route('cotizaciones.show', $cotizacion->id) }}"
                                                class="btn-action bg-primary-soft text-primary" title="Detalles">
                                                <i class='bx bx-show fs-5'></i>
                                            </a>

                                            @if ($cotizacion->estado === 'pendiente')
                                                <a href="{{ route('cotizaciones.edit', $cotizacion->id) }}"
                                                    class="btn-action bg-secondary-subtle text-secondary" title="Editar">
                                                    <i class="bx bx-edit fs-5"></i>
                                                </a>
                                            @endif

                                            @if ($cotizacion->estado === 'aprobada')
                                                @if (!$tieneVenta)
                                                    <button type="button"
                                                        class="btn-action bg-success-subtle text-success border-0"
                                                        onclick="convertirAVenta({{ $cotizacion->id }})"
                                                        title="Generar Venta">
                                                        <i class="bx bx-cart fs-5"></i>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                        class="btn-action bg-secondary-subtle text-secondary border-0"
                                                        disabled title="Ya facturada / asociada a venta">
                                                        <i class="bx bx-check-double fs-5"></i>
                                                    </button>
                                                @endif
                                            @endif

                                            @if ($cotizacion->estado === 'pendiente')
                                                <div class="dropdown">
                                                    <button class="btn-action bg-warning-subtle text-warning border-0"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded fs-5"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                        <li><a class="dropdown-item py-2" href="javascript:void(0)"
                                                                onclick="convertirAVenta({{ $cotizacion->id }})"><i
                                                                    class="fas fa-check text-success me-2"></i> Aprobar y
                                                                Emitir</a>
                                                        </li>
                                                        <li><a class="dropdown-item py-2" href="javascript:void(0)"
                                                                onclick="cambiarEstado({{ $cotizacion->id }}, 'rechazada')"><i
                                                                    class="fas fa-times text-danger me-2"></i> Rechazar</a>
                                                        </li>
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <li><a class="dropdown-item py-2 text-danger"
                                                                href="javascript:void(0)"
                                                                onclick="eliminarCotizacion({{ $cotizacion->id }})"><i
                                                                    class="fas fa-trash-alt me-2"></i> Eliminar</a></li>
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

                <div class="d-flex justify-content-center mt-4">
                    {{ $cotizaciones->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <img src="https://illustrations.popsy.co/flat/searching.svg" alt="no-data" style="width: 200px;"
                        class="mb-4">
                    <h5 class="text-muted">No encontramos cotizaciones</h5>
                    <p class="text-muted small">Prueba cambiando los filtros o crea una nueva propuesta.</p>
                    <a href="{{ route('cotizaciones.create') }}" class="btn btn-primary rounded-pill px-4">
                        Crear mi primera cotización
                    </a>
                </div>
            @endif
        </div>
    </div>

    @include('cotizaciones.partials.modals')
@endsection

@push('scripts')
    <script>
        // Se mantienen tus funciones originales pero usando SweetAlert si está disponible 
        // o confirmaciones más limpias.

        function cambiarEstado(id, estado) {
            const accion = estado === 'aprobada' ? 'aprobar' : 'rechazar';
            if (confirm(`¿Estás seguro de que quieres ${accion} esta cotización?`)) {
                fetch(`/cotizaciones/${id}/cambiar-estado`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            estado: estado
                        })
                    })
                    .then(r => r.json())
                    .then(data => data.success ? location.reload() : alert('Error al cambiar estado'))
                    .catch(e => console.error('Error:', e));
            }
        }

        function convertirAVenta(id) {
            if (confirm('¿Deseas convertir esta cotización en una venta oficial?')) {
                fetch(`/cotizaciones/${id}/convertir-venta`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(r => r.json())
                    .then(data => (data.success && data.redirect) ? window.location.href = data.redirect : alert(data
                        .message))
                    .catch(e => console.error('Error:', e));
            }
        }

        function eliminarCotizacion(id) {
            if (confirm('¿Eliminar definitivamente esta cotización?')) {
                fetch(`/cotizaciones/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(r => r.json())
                    .then(data => data.success ? location.reload() : alert('Error al eliminar'))
                    .catch(e => console.error('Error:', e));
            }
        }
    </script>
@endpush
