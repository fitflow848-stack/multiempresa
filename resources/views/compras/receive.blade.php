@extends('layout.app')

@section('title', 'Recepción de Compra')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-0">Recepción - Ticket #{{ $compra->id }}</h1>
            <small class="text-muted">Registra la recepción de los productos del ticket</small>
        </div>
        <div>
            <a href="{{ route('compras.show', $compra->id) }}" class="btn btn-outline-secondary">Ver Ticket</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <strong>Proveedor</strong>
                            <div>{{ $compra->proveedor_nombre ?? $compra->proveedor_id ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <strong>Fecha Emisión</strong>
                            <div>{{ $compra->fecha_emision ? \Carbon\Carbon::parse($compra->fecha_emision)->format('d/m/Y') : '—' }}</div>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <strong>Total</strong>
                            <div class="fw-bold">S/ {{ number_format($compra->total_pagar ?? 0, 2) }}</div>
                        </div>
                    </div>

                    <h5 class="mt-3">Líneas</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Costo</th>
                                    <th class="text-end">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($compra->lineas as $i => $ln)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $ln->descripcion }}</td>
                                        <td class="text-end">{{ $ln->cantidad }}</td>
                                        <td class="text-end">{{ number_format($ln->costo, 2) }}</td>
                                        <td class="text-end">{{ number_format(($ln->costo ?? 0) * ($ln->cantidad ?? 0), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">Registrar Recepción</div>
                <div class="card-body">
                    <form action="{{ route('recibir-productos.index') }}" method="GET">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Fecha de recepción</label>
                            <input type="date" name="received_at" class="form-control" value="{{ old('received_at', now()->format('Y-m-d')) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones') }}</textarea>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-success">Registrar Recepción</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
