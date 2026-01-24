@extends('layout.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg text-center p-4" style="border-radius: 20px;">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="display-1 text-success">
                            <i class="fas fa-check-circle animate__animated animate__bounceIn"></i>
                        </div>
                    </div>

                    <h3 class="fw-bold text-dark mb-2">¡Registro Exitoso!</h3>
                    <div class="badge bg-light-success text-success px-3 py-2 mb-4" style="font-size: 0.9rem;">
                        <i class="fas fa-info-circle me-1"></i> Se ha realizado su alta correctamente
                    </div>

                    <hr class="my-4 opacity-25">

                    <div class="mb-4">
                        <h5 class="text-secondary mb-1">Ticket Nro. <span class="text-primary fw-bold">{{ $compra->id }}</span></h5>
                        <p class="text-muted small">
                            <i class="fas fa-map-marker-alt me-1"></i> Local: 
                            <select name="local_destino" id="local_destino" class="form-select form-select-sm">
                                @foreach($almacenes as $almacen)
                                    <option value="{{ $almacen->id }}" {{ $compra->local_destino == $almacen->id ? 'selected' : '' }}>
                                        {{ $almacen->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </p>
                    </div>

                    <div class="d-grid gap-3">
                        <a href="{{ route('compras.show', $compra->id) }}" class="btn btn-primary btn-lg shadow-sm py-3" style="background: #0d8b86; border: none; border-radius: 12px;">
                            <i class="fas fa-eye me-2"></i> Ver Detalle del Ticket
                        </a>
                        
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="{{ route('compras.receive', $compra->id) }}" class="btn btn-outline-success w-100 py-2" style="border-radius: 10px;">
                                    <i class="fas fa-file-download me-1"></i> Recibir
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('compras.create') }}" class="btn btn-outline-secondary w-100 py-2" style="border-radius: 10px;">
                                    <i class="fas fa-plus me-1"></i> Nuevo
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('compras.index') }}" class="text-decoration-none text-muted small">
                            <i class="fas fa-list me-1"></i> Volver al listado de presupuestos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilo para el fondo de la página */
    body {
        background-color: #f8f9fc;
    }
    .bg-light-success {
        background-color: #d1e7dd;
    }
    .animate__bounceIn {
        animation-duration: 0.8s;
    }
    .btn-lg:hover {
        filter: brightness(1.1);
        transform: translateY(-1px);
        transition: all 0.2s;
    }
</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(function() {
        // Interceptar clic en el botón Recibir para actualizar local_destino antes de navegar
        $('.btn-outline-success').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const href = $btn.attr('href');
            const localDestino = $('#local_destino').val();

            // Enviar actualización vía AJAX
            $.post('{{ route('compras.update-local', $compra->id) }}', {
                _token: '{{ csrf_token() }}',
                local_destino: localDestino
            }).done(function() {
                // redirigir a la página de recibir
                window.location.href = href;
            }).fail(function() {
                // en caso de error, aún intentar navegar pero informar al usuario
                alert('No se pudo actualizar el local. Intentando continuar.');
                window.location.href = href;
            });
        });
    });
</script>
@endsection