@extends('layout.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2 class="fw-bold text-dark mb-1">Balance General</h2>
                <p class="text-muted mb-0">Estado de Situación Financiera en Tiempo Real</p>
            </div>
            <div class="col-md-6">
                <form action="{{ route('balance.index') }}" method="GET"
                    class="d-flex justify-content-md-end align-items-center">
                    <label for="fecha" class="me-2 fw-bold text-muted">A la fecha:</label>
                    <div class="input-group w-auto">
                        <input type="date" name="fecha" id="fecha" class="form-control" value="{{ $fecha }}">
                        <button type="submit" class="btn btn-primary px-4 bg-gradient-primary">
                            <i class="bx bx-refresh me-1"></i> Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Ecuación Contable Visual -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-white border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="d-flex w-100 text-center text-white fw-bold" style="height: 50px; line-height: 50px;">
                            <div class="flex-fill bg-success bg-gradient" style="width: 50%">ACTIVO: S/
                                {{ number_format($total_activo, 2) }}</div>
                            <div class="flex-fill bg-danger bg-gradient" style="width: 25%">PASIVO: S/
                                {{ number_format($total_pasivo, 2) }}</div>
                            <div class="flex-fill bg-info bg-gradient" style="width: 25%">PATRIMONIO: S/
                                {{ number_format($patrimonio_calculado, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- COLUMNA ACTIVO -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 text-success fw-bold"><i class="bx bx-trending-up me-2"></i> ACTIVO</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-uppercase text-secondary" style="font-size: 0.8rem">Activo
                                            Corriente</th>
                                        <th class="pe-4 py-3 text-end fw-bold text-dark">S/
                                            {{ number_format($total_activo_corriente, 2) }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold text-dark">Caja y Efectivo</div>
                                            <small class="text-muted">Dinero disponible en cajas abiertas</small>
                                        </td>
                                        <td class="pe-4 text-end">S/ {{ number_format($caja, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Bancos</td>
                                        <td class="pe-4 text-end text-muted">S/ {{ number_format($bancos, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold text-dark">Inventario</div>
                                            <small class="text-muted">Mercadería valorizada al costo</small>
                                        </td>
                                        <td class="pe-4 text-end">S/ {{ number_format($inventario, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold text-dark">Cuentas por Cobrar</div>
                                            <small class="text-muted">Créditos pendientes de clientes</small>
                                        </td>
                                        <td class="pe-4 text-end">S/ {{ number_format($cxc, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Anticipo a Proveedores</td>
                                        <td class="pe-4 text-end text-muted">S/
                                            {{ number_format($anticipo_proveedores, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Otros Activos Corrientes</td>
                                        <td class="pe-4 text-end text-muted">S/
                                            {{ number_format($otros_activos_corrientes, 2) }}</td>
                                    </tr>
                                </tbody>
                                <thead class="bg-light border-top">
                                    <tr>
                                        <th class="ps-4 py-3 text-uppercase text-secondary" style="font-size: 0.8rem">Activo
                                            No Corriente</th>
                                        <th class="pe-4 py-3 text-end fw-bold text-dark">S/
                                            {{ number_format($total_activo_no_corriente, 2) }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-4">Activo Fijo</td>
                                        <td class="pe-4 text-end text-muted">S/ {{ number_format($activo_fijo, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Intangibles</td>
                                        <td class="pe-4 text-end text-muted">S/ {{ number_format($intangibles, 2) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-success bg-opacity-10">
                                    <tr>
                                        <th class="ps-4 py-3 text-success">TOTAL ACTIVO</th>
                                        <th class="pe-4 py-3 text-end text-success fw-bolder fs-5">S/
                                            {{ number_format($total_activo, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA PASIVO Y PATRIMONIO -->
            <div class="col-lg-6">
                <div class="d-flex flex-column h-100 gap-4">
                    <!-- PASIVO -->
                    <div class="card border-0 shadow-sm flex-grow-1">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 text-danger fw-bold"><i class="bx bx-trending-down me-2"></i> PASIVO</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 py-3 text-uppercase text-secondary" style="font-size: 0.8rem">
                                                Pasivo Corriente</th>
                                            <th class="pe-4 py-3 text-end fw-bold text-dark">S/
                                                {{ number_format($total_pasivo_corriente, 2) }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold text-dark">Compras a Crédito</div>
                                                <small class="text-muted">Facturas pendientes de pago</small>
                                            </td>
                                            <td class="pe-4 text-end">S/ {{ number_format($compras_credito, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">Adelanto de Clientes</td>
                                            <td class="pe-4 text-end text-muted">S/
                                                {{ number_format($adelanto_clientes, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">Deuda Bancos</td>
                                            <td class="pe-4 text-end text-muted">S/ {{ number_format($deuda_bancos, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">Cuentas por Pagar Terceros</td>
                                            <td class="pe-4 text-end text-muted">S/ {{ number_format($cxp_terceros, 2) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <thead class="bg-light border-top">
                                        <tr>
                                            <th class="ps-4 py-3 text-uppercase text-secondary" style="font-size: 0.8rem">
                                                Pasivo No Corriente</th>
                                            <th class="pe-4 py-3 text-end fw-bold text-dark">S/
                                                {{ number_format($total_pasivo_no_corriente, 2) }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-4">Préstamos a Largo Plazo</td>
                                            <td class="pe-4 text-end text-muted">S/
                                                {{ number_format($otros_pasivos_no_corrientes, 2) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-danger bg-opacity-10">
                                        <tr>
                                            <th class="ps-4 py-3 text-danger">TOTAL PASIVO</th>
                                            <th class="pe-4 py-3 text-end text-danger fw-bolder fs-5">S/
                                                {{ number_format($total_pasivo, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- PATRIMONIO -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 text-info fw-bold"><i class="bx bx-building-house me-2"></i> PATRIMONIO</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="ps-4">Capital Social</td>
                                            <td class="pe-4 text-end text-muted">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">Resultados Acumulados</td>
                                            <td class="pe-4 text-end fw-bold text-dark">S/
                                                {{ number_format($patrimonio_calculado, 2) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-info bg-opacity-10">
                                        <tr>
                                            <th class="ps-4 py-3 text-info">TOTAL PATRIMONIO</th>
                                            <th class="pe-4 py-3 text-end text-info fw-bolder fs-5">S/
                                                {{ number_format($patrimonio_calculado, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- TOTAL FINAL -->
                    <div class="card border-0 shadow-sm bg-dark text-white">
                        <div class="card-body d-flex justify-content-between align-items-center py-3">
                            <h5 class="mb-0 fw-bold">TOTAL PASIVO + PATRIMONIO</h5>
                            <h4 class="mb-0 fw-bold">S/ {{ number_format($total_pasivo + $patrimonio_calculado, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection