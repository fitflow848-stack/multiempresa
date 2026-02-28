@extends('layout.app')
@section('hideSidebar', true)

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="card shadow-lg border-0 overflow-hidden" style="max-width: 500px; width: 100%; border-radius: 1.5rem;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <div class="bg-primary text-white d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-store fa-2x"></i>
                </div>
                <h2 class="fw-bold text-dark">Seleccionar Sucursal</h2>
                <p class="text-muted">Elige la sucursal en la que trabajarás hoy</p>
            </div>

            <form action="{{ route('branch.select.post') }}" method="POST">
                @csrf
                <div class="list-group list-group-flush border rounded-3 mb-4 overflow-hidden">
                    @foreach($branches as $branch)
                        <label class="list-group-item list-group-item-action p-3 d-flex align-items-center cursor-pointer border-bottom">
                            <input class="form-check-input me-3" type="radio" name="branch_id" value="{{ $branch->id }}" required {{ $loop->first ? 'checked' : '' }}>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark">{{ $branch->nombre }}</span>
                                <small class="text-muted">{{ $branch->direccion }}</small>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm" style="border-radius: 0.8rem;">
                        Continuar <i class="fas fa-arrow-right ms-2 small"></i>
                    </button>
                </div>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-decoration-none text-muted small">
                    <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .cursor-pointer { cursor: pointer; }
    .list-group-item-action:hover {
        background-color: #f8f9fa;
    }
    .form-check-input:checked + div span {
        color: #0d6efd !important;
    }
    .card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
    }
</style>
@endsection
