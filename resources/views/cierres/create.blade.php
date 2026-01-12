@extends('layout.app')

@section('title', 'Nuevo Cierre de Caja')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-0">Nuevo Cierre de Caja</h1>
            <small class="text-muted">Registra el cierre de caja del día</small>
        </div>
        <a href="{{ route('cierre-caja.index') }}" class="btn btn-outline-secondary">Volver a Cierres</a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('cierre-caja.store') }}" method="POST" class="row g-3">
                @csrf

                <div class="col-md-4">
                    <label class="form-label">Fecha de cierre</label>
                    <input type="datetime-local" name="fecha_cierre" class="form-control" value="{{ old('fecha_cierre') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Monto apertura</label>
                    <input type="text" name="monto_apertura" class="form-control" value="{{ old('monto_apertura') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Monto cierre</label>
                    <input type="text" name="monto_cierre" class="form-control" value="{{ old('monto_cierre') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Ingresos</label>
                    <input type="text" name="ingresos" class="form-control" value="{{ old('ingresos',0) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Egresos</label>
                    <input type="text" name="egresos" class="form-control" value="{{ old('egresos',0) }}">
                </div>

                <div class="col-12">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="4">{{ old('observaciones') }}</textarea>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary">Guardar cierre</button>
                    <a href="{{ route('cierre-caja.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
