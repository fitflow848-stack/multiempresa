@extends('layout.app')

@section('content')
    {{-- ===================== STYLES ===================== --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/principal.css') }}">

    <div class="almacen-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <div>
                Almacén &gt; Recibir Productos
            </div>
            <div style="float: right;">
                Local: <strong>{{ $company->nombre_comercial }}</strong> | Operador: <strong>{{ $user->name }}</strong>
            </div>
        </div>

        <!-- Título -->
        <div class="page-title">
            Recibir Productos
        </div>


        <!-- Tabla de inventario -->
        <div class="inventory-table">
            <div class="table-header">
                Documentos por recibir
            </div>

            <table class="table table-sm table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Detalle</th>
                        <th>Costo</th>
                        <th>COP</th>
                        <th>MU%</th>
                        <th>MU/D%</th>
                        <th>MUP</th>
                        <th>PVP</th>
                        <th>PA</th>
                        <th>PVP/D</th>
                        <th>PA</th>
                        <th>MUC</th>
                        <th>PVC</th>
                        <th>PA</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @dd($productos) --}}
                    @foreach ($productos as $i => $p)
                        <tr data-producto-id="{{ $p['producto_id'] }}">
                            <td>{{ $i + 1 }}</td>
                            <td class="text-start">{{ $p['nombre'] }}</td>
                            <td>{{ $p['cantidad'] }} NIU</td>
                            <td>{{ $p['detalle'] ?? '-' }}</td>
                            <td>
                                <input type="number" class="form-control form-control-sm" value="{{ $p['costo'] }}">
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm cop" value="{{ $p['cop'] }}">
                            </td>

                            <td>
                                <input type="number" class="form-control form-control-sm mu" value="{{ $p['mu'] }}">
                            </td>

                            <td>
                                <input type="number" class="form-control form-control-sm mud" value="0">
                            </td>

                            <td class="mup">0.00</td>

                            <td>
                                <input type="number" class="form-control form-control-sm pvp" value="{{ $p['pvp'] }}">
                            </td>

                            <td class="pa-pvp text-danger">0.00</td>

                            <td>
                                <input type="number" class="form-control form-control-sm pvpd"
                                    value="{{ $p['pvp'] }}">
                            </td>

                            <td class="pa-pvpd text-danger">0.00</td>

                            <td class="muc">0.00</td>

                            <td>
                                <input type="number" class="form-control form-control-sm pvc" value="{{ $p['pvp'] }}">
                            </td>

                            <td class="pa-pvc text-danger">0.00</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

        <div class="summary-section"
            style="position: absolute; bottom: 20px;  right: 20px; width: auto; padding: 15px; border: 1px solid #ddd; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: flex-end;">
                <div class="summary-stats">
                    <div class="stat-item">
                        <a class="btn btn-primary" href="{{ route('recibir-productos.index') }}">
                            Volver
                        </a>
                    </div>
                    <div class="stat-item">
                        <button class="btn btn-success" id="btn-recibir">
                            Recibir productos
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        function recalcularFila($row) {
            let cop = parseFloat($row.find('.cop').val()) || 0;
            let mu = parseFloat($row.find('.mu').val()) || 0;
            let mud = parseFloat($row.find('.mud').val()) || 0;
            let pvp = parseFloat($row.find('.pvp').val()) || 0;
            let pvpd = parseFloat($row.find('.pvpd').val()) || 0;
            let pvc = parseFloat($row.find('.pvc').val()) || 0;

            // MUP
            let mup = cop * (mu / 100);
            $row.find('.mup').text(mup.toFixed(2));

            // PA PVP
            let paPvp = pvp - cop;
            $row.find('.pa-pvp').text(paPvp.toFixed(2));

            // PA PVP/D
            let paPvpd = pvpd - cop;
            $row.find('.pa-pvpd').text(paPvpd.toFixed(2));

            // MUC
            let muc = cop * (mud / 100);
            $row.find('.muc').text(muc.toFixed(2));

            // PA PVC
            let paPvc = pvc - cop;
            $row.find('.pa-pvc').text(paPvc.toFixed(2));
        }

        $(document).on('input', '.cop, .mu, .mud, .pvp, .pvpd, .pvc', function() {
            let $row = $(this).closest('tr');
            recalcularFila($row);
        });

        // recalcular todo al cargar
        $('tbody tr').each(function() {
            recalcularFila($(this));
        });

        $('#btn-recibir').click(function() {

            let items = [];

            $('tbody tr').each(function() {
                let $tr = $(this);

                items.push({
                    compra_id: {{ $compraId }},
                    producto_id: $tr.data('producto-id'),
                    cantidad: parseFloat($tr.find('td:eq(2)').text()),
                    costo: parseFloat($tr.find('input:eq(0)').val()),
                    cop: parseFloat($tr.find('.cop').val()),
                    mu: parseFloat($tr.find('.mu').val()),
                    mud: parseFloat($tr.find('.mud').val()),
                    mup: parseFloat($tr.find('.mup').text()),
                    pvp: parseFloat($tr.find('.pvp').val()),
                    pvpd: parseFloat($tr.find('.pvpd').val()),
                    pvc: parseFloat($tr.find('.pvc').val())
                });
            });

            $.ajax({
                url: "{{ route('recibir-productos.guardar') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    items: items
                },
                success: function(resp) {
                    Swal.fire('OK', resp.message, 'success')
                        .then(() => location.href = "{{ route('recibir-productos.index') }}");
                },
                error: function(err) {
                    Swal.fire('Error', 'No se pudo guardar', 'error');
                }
            });
        });
    </script>
@endsection
