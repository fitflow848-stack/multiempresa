@extends('layout.app')

@section('title', 'Arqueo de Caja')

@section('content')
<div class="container-fluid bg-white p-3">
	<div class="d-flex justify-content-between align-items-start border-bottom pb-2">
		<div>
			<h5 class="text-danger mb-0">{{ isset($isTesoreria) && $isTesoreria ? 'Apertura de Bóveda / Tesorería' : 'Apertura de Caja' }}</h5>
			<small class="text-muted" id="movimientos_info">{{ isset($isTesoreria) && $isTesoreria ? 'Operaciones de la Bóveda General' : 'Movimientos de caja' }}</small>
		</div>
		<div class="text-end">
			<span class="badge bg-success">ABIERTA</span>
			<div class="small text-muted">{{ now()->format('d/m/Y H:i') }}</div>
		</div>
	</div>

	<form action="{{ route('cierre-caja.store') }}" method="POST" id="arqueoForm" class="mt-4">
		@csrf
		<input type="hidden" name="is_tesoreria" value="{{ isset($isTesoreria) && $isTesoreria ? '1' : '0' }}">
		
		@if(isset($ultimoCierre))
			<div class="alert alert-info alert-dismissible fade show" role="alert">
				<i class="fas fa-info-circle me-2"></i>
				<strong>Saldo inicial automático:</strong> Se ha cargado el monto de cierre de tu último arqueo 
				({{ $ultimoCierre->fecha_cierre->format('d/m/Y H:i') }}) como saldo inicial: 
				<strong>S/ {{ number_format($ultimoCierre->monto_cierre, 2) }}</strong>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		@else
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<i class="fas fa-exclamation-triangle me-2"></i>
				<strong>Primer arqueo:</strong> No se encontraron cierres previos. El saldo inicial se establece en S/ 0.00
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		@endif
		
		<div class="row">
			<div class="col-md-6">
				<div class="mb-4">
					<label class="form-label small fw-bold text-primary" style="font-size: 1rem;">
						🚀 Saldo Inicial en Caja (Efectivo)
						@if(isset($ultimoCierre))
							<div class="small text-muted fw-normal">(Traído del último cierre: {{ $ultimoCierre->fecha_cierre->format('d/m/Y H:i') }})</div>
						@endif
					</label>
					<div class="input-group input-group-lg">
						<span class="input-group-text bg-primary text-white border-primary">S/</span>
						<input type="number" step="0.01" name="monto_apertura" id="saldo_inicial" 
							class="form-control border-primary fw-bold" 
							value="{{ $saldoInicial ?? '0.00' }}" autofocus>
					</div>
					<small class="text-muted mt-1 d-block">Indica cuánto dinero físico hay en la caja en este momento.</small>
				</div>
			</div>

			<div class="col-md-6">
				<div class="mb-2">
					<label class="form-label small fw-bold">Observaciones de Apertura</label>
					<textarea name="observaciones" class="form-control form-control-sm" rows="4" placeholder="Notas sobre el estado de la caja al abrir..."></textarea>
				</div>
				
				{{-- Campos ocultos para mantener compatibilidad con validación del controlador si fuera necesario --}}
				<input type="hidden" name="ingresos" value="0.00">
				<input type="hidden" name="egresos" value="0.00">
				<input type="hidden" name="aportaciones" value="0.00">
				<input type="hidden" name="sustracciones" value="0.00">
				<input type="hidden" name="monto_cierre" value="0.00">
			</div>
		</div>

		<div class="d-flex justify-content-between mt-3">
			<a href="{{ route('pos.index') }}" class="btn btn-outline-secondary btn-sm">↩ Volver TPV</a>
			<button type="submit" class="btn btn-primary btn-sm">💾 Guardar Operación</button>
		</div>
	</form>
</div>

<script>
	document.getElementById('arqueoForm').addEventListener('submit', function(e){
		if (!confirm('¿Confirmar apertura de caja con este saldo inicial?')) { e.preventDefault(); }
	});
</script>



@endsection
