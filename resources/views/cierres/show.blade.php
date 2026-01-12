@extends('layout.app')

@section('title', 'Detalle Cierre')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-0">Detalle Cierre #{{ $cierre->id }}</h1>
            <small class="text-muted">Información completa del cierre de caja</small>
        </div>
        <div>
            <a href="{{ route('cierre-caja.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-4">
                    <strong>Usuario</strong>
                    <div>{{ optional($cierre->user)->name ?? '-' }}</div>
                </div>

                <div class="col-md-4">
                    <strong>Fecha</strong>
                    <div>{{ $cierre->fecha_cierre ? \Carbon\Carbon::parse($cierre->fecha_cierre)->format('d/m/Y H:i') : '-' }}</div>
                </div>

                <div class="col-md-4">
                    <strong>Apertura</strong>
                    <div>S/ {{ number_format($cierre->monto_apertura,2) }}</div>
                </div>

                <div class="col-md-4">
                    <strong>Cierre</strong>
                    <div class="fw-bold">S/ {{ number_format($cierre->monto_cierre,2) }}</div>
                </div>

                <div class="col-md-4">
                    <strong>Ingresos</strong>
                    <div>S/ {{ number_format($cierre->ingresos,2) }}</div>
                </div>

                <div class="col-md-4">
                    <strong>Egresos</strong>
                    <div>S/ {{ number_format($cierre->egresos,2) }}</div>
                </div>

                <div class="col-12">
                    <strong>Observaciones</strong>
                    <div class="border rounded p-2 bg-light">{{ $cierre->observaciones ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
