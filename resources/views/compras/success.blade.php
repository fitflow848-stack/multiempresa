@extends('layout.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container py-5 text-center">
    <div class="alert alert-info d-inline-block px-4 py-2">Se ha realizado su alta</div>

    <h4 class="mt-4">Ticket Nro. {{ $compra->id }}</h4>
    <p class="text-muted">Local {{ $compra->local_destino ?? '—' }}</p>

    <div class="d-flex flex-column align-items-center gap-3 mt-4">
        <a href="{{ route('compras.show', $compra->id) }}" class="btn btn-teal" style="background:#0d8b86;color:#fff">Ver Ticket</a>

        <a href="{{ route('compras.receive', $compra->id) }}" class="btn btn-outline-success">Recibir Ticket</a>

        <a href="{{ route('compras.create') }}" class="btn btn-outline-secondary mt-3">Volver a Presupuestos</a>
    </div>
</div>
@endsection