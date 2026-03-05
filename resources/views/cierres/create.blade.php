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
				<div class="mb-2">
					<label class="form-label small fw-bold">
						Saldo Inicial
						@if(isset($ultimoCierre))
							<small class="text-muted">(último cierre: {{ $ultimoCierre->fecha_cierre->format('d/m/Y H:i') }})</small>
						@endif
					</label>
					<input type="number" step="0.01" name="monto_apertura" id="saldo_inicial" class="form-control form-control-sm" value="{{ $saldoInicial ?? '0.00' }}">
				</div>

				<div class="mb-2">
					<label class="form-label small fw-bold">Ingresos</label>
					<input type="number" step="0.01" name="ingresos" id="ingresos" class="form-control form-control-sm" value="0.00">
				</div>

				<div class="mb-2">
					<label class="form-label small fw-bold">Gastos</label>
					<input type="number" step="0.01" name="egresos" id="gastos" class="form-control form-control-sm" value="0.00">
				</div>

				<div class="mb-2">
					<label class="form-label small fw-bold">Aportaciones</label>
					<input type="number" step="0.01" name="aportaciones" id="aportaciones" class="form-control form-control-sm" value="0.00">
				</div>

				<div class="mb-2">
					<label class="form-label small fw-bold">Sustracciones</label>
					<input type="number" step="0.01" name="sustracciones" id="sustracciones" class="form-control form-control-sm" value="0.00">
				</div>

				<div class="mb-2">
					<label class="form-label small fw-bold">Teórico Cierre</label>
					<input type="text" id="teorico_cierre" readonly class="form-control form-control-sm" value="0.00">
				</div>
			</div>

			<div class="col-md-6">
				<div class="mb-2">
					<label class="form-label small fw-bold">Cierre Caja (Efectivo)</label>
					<input type="number" step="0.01" name="monto_cierre" id="cierre_caja" class="form-control form-control-sm" value="0.00">
				</div>

				<div class="mb-2">
					<label class="form-label small fw-bold" id="label_descuadre">Descuadre Caja</label>
					<input type="text" id="descuadre" readonly class="form-control form-control-sm" value="0.00">
				</div>

				<div class="mb-2">
					<label class="form-label small fw-bold">Observaciones</label>
					<textarea name="observaciones" class="form-control form-control-sm" rows="4"></textarea>
				</div>
			</div>
		</div>

		<div class="d-flex justify-content-between mt-3">
			<a href="{{ route('pos.index') }}" class="btn btn-outline-secondary btn-sm">↩ Volver TPV</a>
			<button type="submit" class="btn btn-primary btn-sm">💾 Guardar Operación</button>
		</div>
	</form>
</div>

<script>
	function val(id){ return parseFloat(document.getElementById(id).value) || 0; }

	function recalcular(){
		const saldoIni = val('saldo_inicial');
		const ingresos = val('ingresos');
		const gastos = val('gastos');
		const aportes = val('aportaciones');
		const sustrac = val('sustracciones');
		const cierreReal = val('cierre_caja');

		const teorico = saldoIni + ingresos - gastos + aportes - sustrac;
		document.getElementById('teorico_cierre').value = teorico.toFixed(2);

		const diferencia = cierreReal - teorico;
		const desc = document.getElementById('descuadre');
		const label = document.getElementById('label_descuadre');
		desc.value = Math.abs(diferencia).toFixed(2);

		if (diferencia < 0) { label.innerText = 'DESCUADRE CAJA: FALTANTE'; desc.style.color = 'red'; }
		else if (diferencia > 0) { label.innerText = 'DESCUADRE CAJA: SOBRANTE'; desc.style.color = 'blue'; }
		else { label.innerText = 'DESCUADRE CAJA'; desc.style.color = 'green'; }
	}

	document.addEventListener('input', function(e){
		const inputs = ['saldo_inicial','ingresos','gastos','aportaciones','sustracciones','cierre_caja'];
		if (inputs.includes(e.target.id)) recalcular();
	});

	document.getElementById('arqueoForm').addEventListener('submit', function(e){
		if (!confirm('¿Confirmar guardado del cierre de caja?')) { e.preventDefault(); }
	});

	// Inicializa cálculo al cargar
	document.addEventListener('DOMContentLoaded', function(){ recalcular(); });
</script>



@endsection
