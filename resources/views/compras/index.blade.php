@extends('layout.app')

@section('content')

<style>
    .page-title { font-weight: 600; }
    .card {
        border: 0;
        box-shadow: 0 4px 12px rgba(0,0,0,.05);
        border-radius: .75rem;
    }
    .card-header {
        background: #f8f9fa;
        font-weight: 600;
    }
    .table thead th {
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 1;
    }
    .summary-box {
        background: #f8fafc;
        border-radius: .75rem;
        padding: 1rem;
    }
    .summary-box .total {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0d6efd;
    }
</style>

<div class="container py-4">

    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="page-title mb-0">Compras</h3>
            <a href="{{ route('compras.create') }}" class="btn btn-primary">Nueva Compra</a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">Listado de Compras</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="compras-table" class="table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Los datos se cargan por AJAX desde DataTables --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ===================== SCRIPTS ===================== --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(function(){
        $('#compras-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ url('/compras/data') }}',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            },
            responsive: true,
            columns: [
                { data: 'id', name: 'id' },
                { data: 'fecha', name: 'fecha' },
                { data: 'proveedor', name: 'proveedor' },
                { data: 'total', name: 'total', render: $.fn.dataTable.render.number('.', ',', 2, '') },
                { data: 'estado', name: 'estado' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
            ],
            order: [[1, 'desc']],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
        });
    });
</script>
@endsection
