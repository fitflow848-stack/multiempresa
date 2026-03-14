@php $isExcel = !empty($is_excel); @endphp

<div class="row mb-4">
    <div class="col-12 text-center border-bottom pb-3">
        <h4 class="text-uppercase fw-bold text-primary">Resumen Financiero</h4>
        <p class="text-muted">
            Periodo: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
        </p>
    </div>
</div>

<div class="row g-4">
    <!-- Ventas -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase opacity-75 mb-1">Ingresos por Ventas</h6>
                        <h3 class="fw-bold mb-0">
                            @if($isExcel)
                                {{ $ventasTotal }}
                            @else
                                S/ {{ number_format($ventasTotal, 2) }}
                            @endif
                        </h3>
                        <small>{{ $ventasCantidad }} comprobantes emitidos</small>
                    </div>
                    <i class="bx bx-trending-up fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Compras -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase opacity-75 mb-1">Inversión en Compras</h6>
                        <h3 class="fw-bold mb-0">
                            @if($isExcel)
                                {{ $comprasTotal }}
                            @else
                                S/ {{ number_format($comprasTotal, 2) }}
                            @endif
                        </h3>
                        <small>{{ $comprasCantidad }} órdenes de compra</small>
                    </div>
                    <i class="bx bx-cart fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Otros Gastos -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase opacity-75 mb-1">Gastos Operativos</h6>
                        <h3 class="fw-bold mb-0">
                            @if($isExcel)
                                {{ $egresos }}
                            @else
                                S/ {{ number_format($egresos, 2) }}
                            @endif
                        </h3>
                        <small>Egresos registrados en caja</small>
                    </div>
                    <i class="bx bx-money fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6 offset-md-3">
        <div class="card border-0 shadow">
            <div class="card-header bg-dark text-white text-center fw-bold py-3">
                ESTIMADO DE RENTABILIDAD
            </div>
            <div class="card-body py-4">
                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                    <span>Ventas (Ingresos):</span>
                    <span class="fw-bold text-success">
                        + @if($isExcel) {{ $ventasTotal }} @else S/ {{ number_format($ventasTotal, 2) }} @endif
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                    <span>Otros Ingresos (Caja):</span>
                    <span class="fw-bold text-success">
                        + @if($isExcel) {{ $ingresosExtra }} @else S/ {{ number_format($ingresosExtra, 2) }} @endif
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                    <span>Compras (Inversión):</span>
                    <span class="fw-bold text-danger">
                        - @if($isExcel) {{ $comprasTotal }} @else S/ {{ number_format($comprasTotal, 2) }} @endif
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                    <span>Gastos (Operativos):</span>
                    <span class="fw-bold text-danger">
                        - @if($isExcel) {{ $egresos }} @else S/ {{ number_format($egresos, 2) }} @endif
                    </span>
                </div>
                
                @php
                    $flujoCaja = ($ventasTotal + $ingresosExtra) - ($comprasTotal + $egresos);
                @endphp
                
                <div class="d-flex justify-content-between mt-4">
                    <h5 class="fw-bold">BALANCE NETO ESTIMADO:</h5>
                    <h5 class="fw-bold {{ $flujoCaja >= 0 ? 'text-success' : 'text-danger' }}">
                        @if($isExcel)
                            {{ $flujoCaja }}
                        @else
                            S/ {{ number_format($flujoCaja, 2) }}
                        @endif
                    </h5>
                </div>
            </div>
            <div class="card-footer bg-light small text-center text-muted">
                * Este balance es un aproximado basado en los flujos de caja y facturación del periodo.
            </div>
        </div>
    </div>
</div>
