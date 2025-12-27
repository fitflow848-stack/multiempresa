@extends('layout.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <div class="container py-4">
        <h3 class="mb-3">Recepción de Ticket #{{ $compra->id }}</h3>

        <div class="card mb-3">
            <div class="card-header">Resumen del Ticket</div>
            <div class="card-body">
                <p><strong>Proveedor:</strong> {{ $compra->proveedor_id ?? '—' }}</p>
                <p><strong>Fecha Emisión:</strong> {{ $compra->fecha_emision ?? '—' }}</p>
                <p><strong>Total a pagar:</strong> S/ {{ number_format($compra->total_pagar, 2) }}</p>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha de recepción</label>
            <input type="date" name="received_at" class="form-control"
                value="{{ old('received_at', now()->format('Y-m-d')) }}">
        </div>

        <div class="mb-3">
            <h6>Lineas</h6>
            <ul>
                @foreach ($compra->lineas as $ln)
                    <li>{{ $ln->descripcion }} — {{ $ln->cantidad }} {{ $ln->vcpc ?? '' }} — S/
                        {{ number_format($ln->costo, 2) }}</li>
                @endforeach
            </ul>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('recibir-productos.index') }}" class="btn btn-success">Registrar Recepción</a>
            <a href="{{ route('compras.show', $compra->id) }}" class="btn btn-outline-secondary">Ver Ticket</a>
        </div>
    </div>
@endsection
