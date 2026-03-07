@extends('layout.app')

@section('title', 'Gráficos del Balance')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Visualización Gráfica del Balance</h1>
            <a href="{{ route('balance.index') }}" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-table fa-sm text-white-50"></i> Ver Tabla
            </a>
        </div>

        <!-- Filtro Fecha (Simplificado) -->
        <div class="card mb-4">
            <div class="card-body py-2">
                <form action="{{ route('balance.graficos') }}" method="GET" class="form-inline justify-content-end"
                    style="display: flex; gap: 10px; align-items: center;">
                    <label class="mr-2 fw-bold">Local:</label>
                    <select name="sucursal_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                        <option value="">TODOS</option>
                        @foreach ($sucursales as $s)
                            <option value="{{ $s->id }}" {{ $sucursal_id == $s->id ? 'selected' : '' }}>
                                {{ $s->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <label class="mr-2 fw-bold">Fecha de Corte:</label>
                    <input type="date" name="fecha" class="form-control form-control-sm mr-2"
                        value="{{ $fecha }}">
                    <button type="submit" class="btn btn-sm btn-primary">Actualizar</button>
                </form>
            </div>
        </div>

        <div class="row">
            <!-- Principal Pie Chart -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Composición General</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie pt-4" style="height: 300px;">
                            <canvas id="myPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activos chart -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">Desglose Activos Corrientes</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-bar" style="height: 300px;">
                            <canvas id="activosChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Pasivos chart -->
            <div class="col-xl-12 col-lg-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-danger">Desglose Pasivos Corrientes</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-bar" style="height: 300px;">
                            <canvas id="pasivosChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- CDN Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Configuración General
            Chart.defaults.font.family = 'Nunito',
                '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
            Chart.defaults.color = '#858796';

            // 1. Pie Chart Data
            var ctxPie = document.getElementById("myPieChart");
            if (ctxPie) {
                var myPieChart = new Chart(ctxPie, {
                    type: 'doughnut',
                    data: {
                        labels: ["Activo Total", "Pasivo Total", "Patrimonio"],
                        datasets: [{
                            data: [{{ $total_activo }}, {{ $total_pasivo }},
                                {{ $patrimonio_calculado }}
                            ],
                            backgroundColor: ['#1cc88a', '#e74a3b', '#4e73df'],
                            hoverBackgroundColor: ['#17a673', '#e02d1b', '#2e59d9'],
                            hoverBorderColor: "rgba(234, 236, 244, 1)",
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        }
                    },
                });
            }

            // 2. Bar Chart Activos
            var ctxActivos = document.getElementById("activosChart");
            if (ctxActivos) {
                var activosChart = new Chart(ctxActivos, {
                    type: 'bar',
                    data: {
                        labels: ["Caja", "Inventario",
                            @foreach ($tiposActivosCorrientes as $t)
                                "{!! addslashes($t->nombre) !!}",
                            @endforeach
                        ],
                        datasets: [{
                            label: "Monto S/",
                            backgroundColor: "#1cc88a",
                            hoverBackgroundColor: "#17a673",
                            borderColor: "#4e73df",
                            data: [{{ $caja }}, {{ $inventario }},
                                @foreach ($tiposActivosCorrientes as $t)
                                    {{ $t->activos_sum_monto ?? 0 }},
                                @endforeach
                            ],
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // 3. Bar Chart Pasivos
            var ctxPasivos = document.getElementById("pasivosChart");
            if (ctxPasivos) {
                var pasivosChart = new Chart(ctxPasivos, {
                    type: 'bar',
                    data: {
                        labels: [
                            @foreach ($tiposPasivosCorrientes as $t)
                                "{!! addslashes($t->nombre) !!}",
                            @endforeach
                        ],
                        datasets: [{
                            label: "Monto S/",
                            backgroundColor: "#e74a3b",
                            hoverBackgroundColor: "#e02d1b",
                            borderColor: "#e74a3b",
                            data: [
                                @foreach ($tiposPasivosCorrientes as $t)
                                    {{ $t->pasivos_sum_monto ?? 0 }},
                                @endforeach
                            ],
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        });
    </script>
@endsection
