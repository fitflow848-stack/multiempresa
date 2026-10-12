@extends('layout.app')

@section('title', 'Ajustar Existencias')

@section('content')
@php $canModificarPrecio = auth()->user()->can('productos.modificar_precio'); @endphp
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-0">Ajuste de Existencias</h1>
                <small class="text-muted">Ajusta cantidades y precios del producto</small>
            </div>
            <div>
                <a href="{{ route('almacen.index') }}" class="btn btn-outline-secondary">← Volver al inventario</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm border-0" style="border-radius: 1rem;">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <h5 class="mb-1">{{ $producto['nombre'] ?? ($producto['nombre'] ?? '-') }}</h5>
                        <small class="text-muted">Código: {{ $producto['codigo'] ?? '-' }}</small>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="badge bg-info text-dark">Existencias Kardex:
                            {{ $producto['existencias_kardex'] ?? '-' }}</div>
                    </div>
                </div>

                <form action="{{ route('almacen.guardar-ajuste', $producto['id'] ?? $producto['id']) }}" method="POST"
                    class="row g-3">
                    @csrf

                    <div class="col-md-4">
                        <label class="form-label">Existencias Físico</label>
                        <input type="number" step="0.01" name="existencias_fisico" class="form-control"
                            value="{{ old('existencias_fisico', $producto['existencias_fisico'] ?? '') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Ajuste Existencias</label>
                        <input type="text" class="form-control" value="{{ $producto['ajuste_existencias'] ?? '-' }}"
                            disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Peso (KGM)</label>
                        <input type="number" step="0.01" name="peso" class="form-control"
                            value="{{ old('peso', $producto['peso'] ?? '') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Precio Compra</label>
                        <input type="number" step="0.01" name="precio_compra" class="form-control {{ !$canModificarPrecio ? 'bg-light' : '' }}"
                            value="{{ old('precio_compra', $producto['precio_compra'] ?? '') }}" {{ !$canModificarPrecio ? 'readonly' : '' }} required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Costo Operativo</label>
                        <input type="number" step="0.01" name="costo_operativo" class="form-control {{ !$canModificarPrecio ? 'bg-light' : '' }}"
                            value="{{ old('costo_operativo', $producto['costo_operativo'] ?? '') }}" {{ !$canModificarPrecio ? 'readonly' : '' }}>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-2">Precios de Venta
                            @if(!$canModificarPrecio)
                                <span class="badge bg-secondary ms-2" style="font-size:0.7rem;">Solo lectura — sin permiso modificar_precio</span>
                            @endif
                        </h6>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">PVP</label>
                        <input type="number" step="0.01" name="pvp" class="form-control {{ !$canModificarPrecio ? 'bg-light' : '' }}"
                            value="{{ old('pvp', $producto['pvp'] ?? '') }}" {{ !$canModificarPrecio ? 'readonly' : '' }} required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">PVP/Dcto.</label>
                        <input type="number" step="0.01" name="pvp_dcto" class="form-control {{ !$canModificarPrecio ? 'bg-light' : '' }}"
                            value="{{ old('pvp_dcto', $producto['pvp_dcto'] ?? '') }}" {{ !$canModificarPrecio ? 'readonly' : '' }}>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">PVC</label>
                        <input type="number" step="0.01" name="pvc" class="form-control {{ !$canModificarPrecio ? 'bg-light' : '' }}"
                            value="{{ old('pvc', $producto['pvc'] ?? '') }}" {{ !$canModificarPrecio ? 'readonly' : '' }}>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">PVC/Dcto.</label>
                        <input type="number" step="0.01" name="pvc_dcto" class="form-control {{ !$canModificarPrecio ? 'bg-light' : '' }}"
                            value="{{ old('pvc_dcto', $producto['pvc_dcto'] ?? '') }}" {{ !$canModificarPrecio ? 'readonly' : '' }}>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">PV/Docena</label>
                        <input type="number" step="0.01" name="pv_docena" class="form-control {{ !$canModificarPrecio ? 'bg-light' : '' }}"
                            value="{{ old('pv_docena', $producto['pv_docena'] ?? '') }}" {{ !$canModificarPrecio ? 'readonly' : '' }}>
                    </div>

                    <div class="col-md-8" id="boxLoteQuitar">
                        <label class="form-label">Cantidad a descontar por lote
                            <small class="text-muted">(la suma debe ser igual a lo que reduces)</small></label>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Lote</th>
                                        <th>Fecha</th>
                                        <th class="text-end">Disponible</th>
                                        <th class="text-end">Costo</th>
                                        <th style="width:130px;">Descontar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lotes as $l)
                                        <tr>
                                            <td>{{ $l->lote ?: 'Sin nombre' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($l->fecha)->format('d/m/Y') }}</td>
                                            <td class="text-end">{{ rtrim(rtrim(number_format($l->cantidad, 2, '.', ''), '0'), '.') }}</td>
                                            <td class="text-end">S/. {{ number_format($l->costo, 2) }}</td>
                                            <td>
                                                <input type="number" step="0.01" min="0" max="{{ $l->cantidad }}"
                                                    name="lotes_cant[{{ $l->id }}]" class="form-control form-control-sm lote-cant"
                                                    data-max="{{ $l->cantidad }}" value="{{ old('lotes_cant.' . $l->id) }}" placeholder="0">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small id="resumenLotes" class="fw-semibold"></small>
                            <button type="button" id="btnAutoLotes" class="btn btn-sm btn-outline-secondary">Autocompletar (más antiguos primero)</button>
                        </div>
                        @php $sumaLotes = round($lotes->sum('cantidad'), 2); $stockTotal = round((float) ($producto['existencias_kardex'] ?? 0), 2); @endphp
                        @if (abs($sumaLotes - $stockTotal) > 0.001)
                            <small class="text-warning d-block mt-1">
                                Los lotes suman {{ rtrim(rtrim(number_format($sumaLotes, 2, '.', ''), '0'), '.') }} y el stock total es
                                {{ rtrim(rtrim(number_format($stockTotal, 2, '.', ''), '0'), '.') }}: hay movimientos negativos
                                (por ejemplo anulaciones) que no se muestran como lote.
                            </small>
                        @endif
                    </div>

                    <div class="col-md-6" id="boxLoteNuevo">
                        <label class="form-label">Nombre del nuevo lote</label>
                        <input type="text" name="lote_nuevo" class="form-control" maxlength="100"
                            value="{{ old('lote_nuevo') }}" placeholder="Opcional. Se genera un lote nuevo con los precios indicados">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Observación</label>
                        <textarea name="observacion" class="form-control" rows="2" placeholder="Ej: Ajuste por rotura, ingreso inicial, etc.">{{ old('observacion') }}</textarea>
                    </div>

                    <div class="col-12 d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">Guardar Ajuste</button>
                        <a href="{{ route('almacen.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>


        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const inputFisico = document.querySelector('input[name="existencias_fisico"]');
                const inputAjuste = document.querySelector('input[disabled]'); // El campo de ajuste
                const kardexOriginal = {{ $producto['existencias_kardex'] ?? 0 }};

                function actualizarResumenLotes() {
                    const necesario = Math.max(0, kardexOriginal - (parseFloat(inputFisico.value) || 0));
                    let suma = 0;
                    document.querySelectorAll('.lote-cant').forEach(function(i) { suma += parseFloat(i.value) || 0; });
                    const el = document.getElementById('resumenLotes');
                    if (necesario <= 0.0001) { el.textContent = ''; return; }
                    const falta = Math.round((necesario - suma) * 100) / 100;
                    el.textContent = 'Asignado ' + suma.toFixed(2) + ' de ' + necesario.toFixed(2) +
                        (falta > 0 ? ' · faltan ' + falta.toFixed(2) : (falta < 0 ? ' · te pasas por ' + Math.abs(falta).toFixed(2) : ' · completo'));
                    el.style.color = Math.abs(falta) < 0.005 ? 'green' : 'crimson';
                }
                document.querySelectorAll('.lote-cant').forEach(function(i) {
                    i.addEventListener('input', actualizarResumenLotes);
                });
                document.getElementById('btnAutoLotes').addEventListener('click', function() {
                    let restante = Math.max(0, kardexOriginal - (parseFloat(inputFisico.value) || 0));
                    document.querySelectorAll('.lote-cant').forEach(function(i) {
                        const toma = Math.min(restante, parseFloat(i.dataset.max) || 0);
                        i.value = toma > 0 ? (Math.round(toma * 100) / 100) : '';
                        restante -= toma;
                    });
                    actualizarResumenLotes();
                });
                inputFisico.closest('form').addEventListener('submit', function(e) {
                    const necesario = Math.max(0, kardexOriginal - (parseFloat(inputFisico.value) || 0));
                    if (necesario <= 0.0001) return;
                    let suma = 0;
                    document.querySelectorAll('.lote-cant').forEach(function(i) { suma += parseFloat(i.value) || 0; });
                    if (Math.abs(suma - necesario) > 0.005) {
                        e.preventDefault();
                        alert('Debes repartir exactamente ' + necesario.toFixed(2) + ' entre los lotes. Asignado: ' + suma.toFixed(2));
                    }
                });
                inputFisico.addEventListener('input', function() {
                    const fisico = parseFloat(this.value) || 0;
                    const diferencia = fisico - kardexOriginal;

                    // Formatear con color si es positivo o negativo
                    inputAjuste.value = diferencia.toFixed(2);
                    inputAjuste.style.color = diferencia >= 0 ? 'green' : 'red';

                    const quitar = diferencia < -0.0001;
                    document.querySelectorAll('.lote-cant').forEach(function(i) {
                        i.disabled = !quitar;
                        if (!quitar) i.value = '';
                    });
                    document.getElementById('btnAutoLotes').disabled = !quitar;
                    document.querySelector('input[name="lote_nuevo"]').disabled = diferencia <= 0.0001;
                    actualizarResumenLotes();
                });
                inputFisico.dispatchEvent(new Event('input'));
            });
        </script>

    @endsection
