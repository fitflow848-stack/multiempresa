@extends('layout.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <div class="container py-4">
        <h3>Ticket #{{ $compra->id }}</h3>

        <div class="row">
            <div class="col-md-8">
                <p><strong>Proveedor:</strong> {{ $compra->proveedor_id ?? '—' }}</p>
                <p><strong>Fecha Emisión:</strong> {{ $compra->fecha_emision ?? '—' }}</p>
                <p><strong>Tipo:</strong> {{ $compra->tipo ?? '—' }}</p>

                <h5 class="mt-4">Detalle</h5>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Costo</th>
                            <th>Importe</th>
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

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Resumen</div>
                    <div class="card-body">
                        <p><strong>Total Bruto:</strong> S/ {{ number_format($compra->total_bruto, 2) }}</p>
                        <p><strong>Total Descuento:</strong> S/ {{ number_format($compra->total_descuento, 2) }}</p>
                        <p><strong>Total Impuesto:</strong> S/ {{ number_format($compra->total_impuesto, 2) }}</p>
                        <p><strong>Total a Pagar:</strong> S/ {{ number_format($compra->total_pagar, 2) }}</p>
                        @php
                            use Illuminate\Support\Carbon;
                            $received = null;
                            if (!empty($compra->received_at)) {
                                // Si ya es Carbon lo deja, si es string lo parsea
                                $received =
                                    $compra->received_at instanceof \DateTime
                                        ? $compra->received_at
                                        : Carbon::parse($compra->received_at);
                            }
                        @endphp
                        <p><strong>Recepción:</strong>
                            {{ $received ? $received->format('Y-m-d H:i') : 'No recibido' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('compras.create') }}" class="btn btn-outline-secondary">Volver</a>
            <a href="{{ route('compras.receive', $compra->id) }}" class="btn btn-primary">Recibir Ticket</a>
        </div>
    </div>
@endsection
