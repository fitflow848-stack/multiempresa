@extends('layout.app')

@section('title', 'Listado de Guías de Remisión')

@section('content')
    <style>
        :root {
            --primary-soft: #f8faff;
            --accent-color: #6366f1;
            --sunat-blue: #006BB6;
            --status-bg: #f1f5f9;
        }

        .bg-gradient-guia {
            background: linear-gradient(135deg, #006BB6 0%, #00aaff 100%);
        }

        .table-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.04);
            border: 1px solid #f1f5f9;
        }

        .badge-motivo {
            padding: 5px 12px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.7rem;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            text-transform: uppercase;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .status-pendiente { background: #fff7ed; color: #9a3412; border: 1px solid #fed7aa; }
        .status-procesado { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .status-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        .guia-id {
            font-family: 'Inter', sans-serif;
            font-weight: 800;
            color: #1e293b;
            font-size: 0.95rem;
        }

        .guia-serie {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
        }

        .btn-action {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            transition: all 0.2s;
            background: white;
            border: 1px solid #e2e8f0;
            color: #64748b;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            color: var(--sunat-blue);
            border-color: var(--sunat-blue);
        }

        .btn-action i { font-size: 1.1rem; }

        .btn-action.btn-send { color: #0ea5e9; border-color: #bae6fd; background: #f0f9ff; }
        .btn-action.btn-consult { color: #f59e0b; border-color: #fef3c7; background: #fffbeb; }
        .btn-action.btn-pdf { color: #ef4444; border-color: #fee2e2; background: #fef2f2; }
        .btn-action.btn-xml { color: #6366f1; border-color: #e0e7ff; background: #eef2ff; }

        .thead-custom {
            background-color: #f8fafc;
        }

        .thead-custom th {
            color: #475569 !important;
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            padding: 18px !important;
        }

        .empty-state {
            padding: 60px 0;
            text-align: center;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">Guías de Remisión</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('principal.index') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Guías</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('guia-transporte.add') }}" class="btn btn-primary px-4 py-2 shadow-sm bg-gradient-guia border-0 rounded-3">
                <i class="fas fa-plus-circle me-2"></i>Nueva Guía Remitente
            </a>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="table">
                    <thead class="thead-custom">
                        <tr>
                            <th>NRO. GUÍA</th>
                            <th>FECHA EMISIÓN</th>
                            <th>DESTINATARIO</th>
                            <th>MOTIVO</th>
                            <th>ESTADO SUNAT</th>
                            <th class="text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Ticker -->
    <div class="modal fade" id="modalTicker" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Detalle de SUNAT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4" id="modalTickerBody">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
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
                "order": [[1, "desc"]],
                "dom": '<"d-flex justify-content-between mb-4"f>rt<"d-flex justify-content-between mt-4"ip>',
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                },
                "columns": [
                    {
                        "data": "numero",
                        "render": function(data, type, row) {
                            const serie = row.serie || 'T001';
                            const num = String(data || row.id).padStart(8, '0');
                            return `<div class="d-flex flex-column">
                                        <span class="guia-id">${serie}-${num}</span>
                                        <span class="text-muted small">ID: #${row.id}</span>
                                    </div>`;
                        }
                    },
                    {
                        "data": "fecha_traslado",
                        "render": function(data) {
                            return `<div class="text-dark fw-medium">
                                        <i class="far fa-calendar-alt me-2 text-muted"></i>${moment(data).format('DD/MM/YYYY')}
                                    </div>`;
                        }
                    },
                    {
                        "data": "cliente_nombre",
                        "render": function(data, type, row) {
                            return `<div class="d-flex flex-column">
                                        <span class="fw-bold text-dark">${data || 'CLIENTE NO ESPECIFICADO'}</span>
                                        <span class="text-muted small">${row.direccion_llegada || '-'}</span>
                                    </div>`;
                        }
                    },
                    {
                        "data": "motivo_traslado_codigo",
                        "render": function(data) {
                            let texto = 'Venta';
                            if(data == '01') texto = 'VENTA';
                            else if(data == '02') texto = 'COMPRA';
                            return `<span class="badge-motivo">${texto}</span>`;
                        }
                    },
                    {
                        "data": "sunat_status",
                        "render": function(data, type, row) {
                            let badge = 'status-pendiente';
                            let icon = 'fas fa-clock';
                            let text = 'PENDIENTE';

                            if (row.ticker) {
                                badge = 'status-procesado';
                                icon = 'fas fa-check-double';
                                text = 'ACEPTADO';
                            } else if (data === 'PROCESADO') {
                                text = 'POR CONSULTAR';
                            }

                            return `<span class="badge-status ${badge}">
                                        <i class="${icon} me-1"></i> ${text}
                                    </span>`;
                        }
                    },
                    {
                        "data": null,
                        "className": "text-center",
                        "render": function(data, type, row) {
                            let pdfBtn = `<a href="{{ url('/guia/remision') }}/${row.id}" target="_blank" class="btn-action btn-pdf" title="PDF"><i class="bx bx-file"></i></a>`;
                            let xmlBtn = '';
                            let mainBtn = '';

                            if (row.nombre_archivo) {
                                xmlBtn = `<a href="{{ asset('storage/xml/guias') }}/${row.nombre_archivo}.xml" target="_blank" class="btn-action btn-xml" title="XML"><i class="bx bx-file"></i></a>`;
                                
                                if (!row.ticker) {
                                    mainBtn = `<button class="btn-action btn-send btn-send-sunat-guia" data-id="${row.id}" title="Enviar a SUNAT"><i class="bx bx-send"></i></button>`;
                                } else {
                                    mainBtn = `<button class="btn-action btn-consult btn-consult-sunat-guia" data-ticker="${row.ticker}" data-id="${row.id}" title="Consultar Estado"><i class="bx bx-sync"></i></button>`;
                                }
                            } else {
                                mainBtn = `<button class="btn-action btn-send" disabled title="Sin XML"><i class="bx bx-error-alt"></i></button>`;
                            }

                            return `<div class="d-flex justify-content-center gap-2">
                                        ${pdfBtn}
                                        ${xmlBtn}
                                        ${mainBtn}
                                    </div>`;
                        }
                    }
                ]
            });

            // Acción: Enviar a SUNAT
            $(document).on('click', '.btn-send-sunat-guia', function() {
                const id = $(this).data('id');
                const btn = $(this);

                Swal.fire({
                    title: '¿Enviar Guía?',
                    text: 'Se enviará el documento electrónico a SUNAT para su validación.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, Enviar Ahora',
                    cancelButtonText: 'Después',
                    confirmButtonColor: '#006BB6',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                        $.post(`{{ url('/guia/sendSunat') }}/${id}`, { _token: '{{ csrf_token() }}' })
                        .done(res => {
                            if (res.estado) {
                                Swal.fire('Enviado', 'La guía se envió correctamente. Ticket generado: ' + res.ticker, 'success');
                                tabla.ajax.reload();
                            } else {
                                Swal.fire('Error SUNAT', res.mensaje || 'Hubo un problema al enviar.', 'error');
                                btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i>');
                            }
                        })
                        .fail(() => {
                            Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
                            btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i>');
                        });
                    }
                });
            });

            // Acción: Consultar Ticker
            $(document).on('click', '.btn-consult-sunat-guia', function() {
                const ticker = $(this).data('ticker');
                const btn = $(this);

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.get(`{{ url('/guia/consultar-ticker') }}/${ticker}`)
                .done(res => {
                    let iconResponse = 'info';
                    let titleResponse = 'Estado del Ticket';
                    
                    if (res && res.estado) {
                        iconResponse = 'success';
                        titleResponse = 'Aceptado por SUNAT';
                    }

                    Swal.fire({
                        icon: iconResponse,
                        title: titleResponse,
                        text: res.mensaje || (res.estado ? 'La guía ha sido procesada correctamente' : 'El documento sigue en proceso')
                    });
                })
                .fail(() => Swal.fire('Error', 'Error al consultar el servidor', 'error'))
                .always(() => {
                    btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i>');
                });
            });
        });
    </script>
@endsection
