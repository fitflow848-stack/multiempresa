@extends('layout.app')

@section('title', 'Transferencia Exitosa')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card shadow-lg border-0 py-5" style="border-radius: 20px;">
                <div class="card-body">
                    <div class="mb-4">
                        <i class="fas fa-check-circle fa-5x text-success"></i>
                    </div>
                    <h2 class="fw-bold mb-3">¡Transferencia Realizada!</h2>
                    <p class="text-muted mb-4">Se han transferido los productos correctamente entre las sucursales seleccionadas.</p>
                    
                    <div class="bg-light p-4 rounded-4 mb-4 text-start">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <span class="text-muted small d-block">Código de Operación</span>
                                <strong class="fs-5">{{ $codigo }}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <span class="text-muted small d-block">Local Destino</span>
                                <strong class="fs-5 text-primary">{{ $transferencias->first()->sucursalDestino->nombre }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-center">
                        <a href="{{ route('almacen.transferencia.pdf', $codigo) }}" target="_blank" class="btn btn-outline-dark px-4 py-2">
                            <i class="fas fa-print me-2"></i>Imprimir Documento
                        </a>
                        <a href="{{ route('almacen.index') }}" class="btn btn-primary px-4 py-2">
                            Ir al Almacén
                        </a>
                        <a href="{{ route('almacen.transferir') }}" class="btn btn-success px-4 py-2">
                            Nueva Transferencia
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
