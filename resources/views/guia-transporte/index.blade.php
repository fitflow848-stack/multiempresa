@extends('layout.app')

@section('title', 'Listado de Guías de Remisión')

@section('content')
    <style>
        :root {
            --primary-soft: #eef2ff;
            --accent-color: #6366f1;
            --sunat-blue: #006BB6;
        }

        .bg-gradient-guia {
            background: linear-gradient(135deg, #006BB6 0%, #00aaff 100%);
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        }

        /* Estilo para las etiquetas de Motivo */
        .badge-motivo {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.75rem;
            background: var(--primary-soft);
            color: var(--sunat-blue);
            border: 1px solid #d0e1fd;
        }

        .guia-id {
            font-weight: 700;
            color: #1e293b;
        }

        .guia-date {
            font-size: 0.8rem;
            color: #64748b;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 0.65rem 1rem;
        }

        .form-control:focus {
            border-color: var(--sunat-blue);
            box-shadow: 0 0 0 3px rgba(0, 107, 182, 0.1);
        }

        /* DataTables Custom */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--sunat-blue) !important;
            color: white !important;
            border-radius: 8px;
            border: none;
        }
        
        .thead-custom {
            background-color: #f8fafc;
            border-bottom: 2px solid #edf2f7;
        }
        
        .thead-custom th {
            color: #64748b !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
            padding: 15px !important;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2 class="fw-bold text-dark mb-1">Guías de Remisión</h2>
                <p class="text-muted mb-0">Gestión de traslados y documentos electrónicos</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <a href="{{ route('guia-transporte.add') }}" 
                   class="btn btn-primary px-4 py-2 shadow-sm bg-gradient-guia border-0">
                    <i class="fas fa-plus-circle me-2"></i>Crear Nueva Guía
                </a>
            </div>
        </div>

        <div class="table-container shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="table">
                    <thead class="thead-custom">
                        <tr>
                            <th class="border-0">NRO. GUÍA</th>
                            <th class="border-0">FECHA EMISIÓN</th>
                            <th class="border-0">MOTIVO TRASLADO</th>
                            <th class="border-0">OBSERVACIONES</th>
                            <th class="border-0 text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <script>
        $(document).ready(function() {
            let tabla = $('#table').DataTable({
                "ajax": {
                    "url": '{{ route('guia.getAll') }}',
                    "dataSrc": ""
                },
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "dom": '<"d-flex justify-content-between mb-3"f>rt<"d-flex justify-content-between mt-3"ip>',
                "columns": [
                    { 
                        "data": "id",
                        "render": function(data, type, row) {
                            return `<div class="d-flex flex-column">
                                        <span class="guia-id">#${data}</span>
                                        <span class="text-muted small">T001-${data}</span>
                                    </div>`;
                        }
                    },
                    { 
                        "data": "created_at",
                        "render": function(data) {
                            return `<div class="guia-date">
                                        <i class="far fa-calendar-alt me-1"></i> ${moment(data).format('DD/MM/YYYY')}
                                    </div>`;
                        }
                    },
                    { 
                        "data": "motivo_traslado",
                        "render": function(data) {
                            return `<span class="badge-motivo">${data || 'Venta'}</span>`;
                        }
                    },
                    { 
                        "data": "observacion",
                        "render": function(data) {
                            return `<span class="text-muted" style="font-size:0.85rem">${data || '-'}</span>`;
                        }
                    },
                    {
                        "data": null,
                        "className": "text-center",
                        "render": function(data, type, row) {
                            return `
                                <div class="btn-group">
                                    <a class="btn btn-white btn-sm border shadow-sm px-3 rounded-pill text-danger fw-bold" 
                                       href="{{ url('/guia/remision') }}/${row.id}" 
                                       target="_blank">
                                        <i class="fas fa-file-pdf me-1"></i> PDF
                                    </a>
                                </div>`;
                        }
                    }
                ]
            });
        });
    </script>
@endsection