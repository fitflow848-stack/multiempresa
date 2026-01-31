@extends('layout.app')

@section('title', 'Area Reportes')

@section('content')
    <div class="container-fluid py-3">
        <!-- Header -->
        <div class="row mb-3">
            <div class="col-12">
                <h4 class="text-secondary border-bottom pb-2">Area Reportes</h4>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body bg-light">
                <form id="filterForm">
                    <div class="row g-3">
                        <!-- Row 1 -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Desde:</label>
                            <input type="date" class="form-control form-control-sm" name="desde"
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Hasta:</label>
                            <input type="date" class="form-control form-control-sm" name="hasta"
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Local:</label>
                            <select class="form-select form-select-sm" name="local_id">
                                <option value="">Actual</option>
                                @foreach ($locales as $local)
                                    <option value="{{ $local->id }}">{{ $local->nombre ?? $local->razon_social }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Row 2: Main Selector -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-primary">REPORTES:</label>
                            <select class="form-select" name="report_id" id="reportSelector">
                                @foreach ($reportTypes as $category => $reports)
                                    <optgroup label="{{ ucfirst($category) }}">
                                        @foreach ($reports as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end gap-2">
                            <button type="button" class="btn btn-dark" id="btnSearch">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button type="button" class="btn btn-danger" id="btnPrint">
                                <i class="fas fa-file-pdf"></i> Imprimir
                            </button>
                            <button type="button" class="btn btn-success" id="btnExport">
                                <i class="fas fa-file-csv"></i> Exportar
                            </button>
                        </div>

                        <!-- Row 3: Secondary Filters -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Familia:</label>
                            <select class="form-select form-select-sm" name="familia_id">
                                <option value="">Todos</option>
                                @foreach ($familias as $familia)
                                    <option value="{{ $familia->id }}">{{ $familia->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Tipo Comprobante:</label>
                            <select class="form-select form-select-sm" name="tipo_comprobante">
                                <option value="">Todos</option>
                                <option value="boleta">Boleta</option>
                                <option value="factura">Factura</option>
                                <option value="ticket">Ticket</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Vendedor:</label>
                            <select class="form-select form-select-sm" name="vendedor_id">
                                <option value="">Todos</option>
                                @foreach ($vendedores as $vendedor)
                                    <option value="{{ $vendedor->id }}">{{ $vendedor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Codigo Barras:</label>
                            <input type="text" class="form-control form-control-sm" name="codigo_barras">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results -->
        <div id="resultsContainer" class="card shadow-sm border-0 min-vh-50">
            <div class="card-body text-center py-5">
                <h5 class="text-muted">Seleccione un reporte y haga clic en Buscar</h5>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('reportSelector').addEventListener('change', function() {
                document.getElementById('btnSearch').click();
            });

            document.getElementById('btnSearch').addEventListener('click', function() {
                // Show loading
                document.getElementById('resultsContainer').innerHTML =
                    '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary"></i><br>Cargando...</div>';

                const form = document.getElementById('filterForm');
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);

                fetch("{{ route('reportes.busqueda') }}?" + params.toString())
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('resultsContainer').innerHTML = html;
                    })
                    .catch(err => {
                        document.getElementById('resultsContainer').innerHTML =
                            '<div class="alert alert-danger">Error al cargar reporte</div>';
                    });
            });
        </script>
    @endpush
@endsection
