@extends('layout.app')

@section('title', 'Procesar Recepción')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-0">Procesar Recepción - Ticket #{{ $compra->id }}</h1>
            <small class="text-muted">Marca las líneas recibidas y confirma el ingreso</small>
        </div>
        <div>
            <a href="{{ route('compras.show', $compra->id) }}" class="btn btn-outline-secondary">Volver al ticket</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
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

            <form action="{{ route('compras.receive.products.store', $compra->id) }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Producto / Descripción</th>
                                <th class="text-center" style="width:120px">Cant.</th>
                                <th class="text-center" style="width:140px">Recibir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($compra->lineas as $ln)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $ln->descripcion ?? ($ln->product_id ? 'Producto #'.$ln->product_id : '-') }}</td>
                                    <td class="text-center">{{ $ln->cantidad }}</td>
                                    <td class="text-center">
                                        <input type="number" name="recibido[{{ $ln->id }}]" min="0" max="{{ $ln->cantidad }}" value="{{ old('recibido.'.$ln->id, $ln->cantidad) }}" class="form-control" style="max-width:120px; margin:0 auto;">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-success">Recibir Productos</button>
                    <a href="{{ route('compras.show', $compra->id) }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
