@extends('layout.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container py-4">
    <h3 class="mb-3">Procesar Recepción ({{ $index + 1 }}) - Ticket #{{ $compra->id }}</h3>

    <div class="card mb-3">
        <div class="card-header">Resumen del Ticket</div>
        <div class="card-body">
            <p><strong>Proveedor:</strong> {{ $compra->proveedor_id ?? '—' }}</p>
            <p><strong>Fecha Emisión:</strong> {{ $compra->fecha_emision ?? '—' }}</p>
            <p><strong>Total a pagar:</strong> S/ {{ number_format($compra->total_pagar,2) }}</p>
        </div>
    </div>

    <form action="{{ route('compras.receive.batch.next') }}" method="POST">
        @csrf

        <div class="mb-3">
            <h6>Lineas a recibir</h6>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto / Descripción</th>
                        <th>Cantidad</th>
                        <th>CB / Producto ID</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compra->lineas as $ln)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $ln->descripcion ?? ($ln->product_id ? 'Producto #'.$ln->product_id : '') }}</td>
                            <td>{{ $ln->cantidad }}</td>
                            <td>{{ $ln->cb ?? $ln->product_id }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">Recibir y siguiente</button>
            <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
