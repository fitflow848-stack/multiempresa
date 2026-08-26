@extends('layout.app')

@section('content')
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
        }

        .dashboard {
            padding: 24px;
            background: linear-gradient(135deg, #f0f4ff 0%, #fafbff 100%);
            min-height: 100vh;
        }

        /* Header Section */
        .dashboard-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            padding: 24px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .company-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .company-logo {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .company-details h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .company-details p {
            color: var(--gray);
            margin: 4px 0;
            font-size: 0.9rem;
        }

        .quick-actions {
            display: flex;
            gap: 12px;
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .quick-action-btn.primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }

        .quick-action-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
        }

        .quick-action-btn.secondary {
            background: white;
            color: var(--dark);
            border: 2px solid #e2e8f0;
        }

        .quick-action-btn.secondary:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Stats Cards Row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .stat-card.blue::before {
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
        }

        .stat-card.green::before {
            background: linear-gradient(90deg, #10b981, #34d399);
        }

        .stat-card.purple::before {
            background: linear-gradient(90deg, #8b5cf6, #a78bfa);
        }

        .stat-card.orange::before {
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
        }

        .stat-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .stat-card.blue .icon {
            background: #eff6ff;
            color: #3b82f6;
        }

        .stat-card.green .icon {
            background: #ecfdf5;
            color: #10b981;
        }

        .stat-card.purple .icon {
            background: #f5f3ff;
            color: #8b5cf6;
        }

        .stat-card.orange .icon {
            background: #fffbeb;
            color: #f59e0b;
        }

        .stat-card .label {
            font-size: 0.875rem;
            color: var(--gray);
            font-weight: 500;
            margin-bottom: 8px;
        }

        .stat-card .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
        }

        .stat-card .comparison {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.8rem;
            margin-top: 8px;
        }

        .stat-card .comparison.up {
            color: var(--success);
        }

        .stat-card .comparison.down {
            color: var(--danger);
        }

        /* Charts Grid */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .chart-card h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-card h3 i {
            color: var(--primary);
        }

        /* Bottom Grid */
        .bottom-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .info-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .info-card h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f1f5f9;
        }

        .info-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 12px;
            background: #f8fafc;
            border-radius: 10px;
            transition: all 0.2s;
        }

        .info-item:hover {
            background: #f1f5f9;
        }

        .info-item .key {
            color: var(--gray);
            font-size: 0.875rem;
        }

        .info-item .value {
            font-weight: 600;
            color: var(--dark);
        }

        .info-item .value.success {
            color: var(--success);
        }

        .info-item .value.danger {
            color: var(--danger);
        }

        .info-item .value.warning {
            color: var(--warning);
        }

        /* Top Products Table */
        .products-table {
            width: 100%;
        }

        .products-table tr {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .products-table tr:last-child {
            border-bottom: none;
        }

        .products-table .rank {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8rem;
            margin-right: 12px;
        }

        .products-table .rank.gold {
            background: #fef3c7;
            color: #d97706;
        }

        .products-table .rank.silver {
            background: #f1f5f9;
            color: #64748b;
        }

        .products-table .rank.bronze {
            background: #fed7aa;
            color: #c2410c;
        }

        .products-table .rank.normal {
            background: #f8fafc;
            color: #94a3b8;
        }

        .products-table .name {
            flex: 1;
            font-weight: 500;
            color: var(--dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .products-table .qty {
            font-weight: 600;
            color: var(--primary);
            margin-left: 12px;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .bottom-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .company-info {
                flex-direction: column;
            }

            .quick-actions {
                flex-wrap: wrap;
                justify-content: center;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="dashboard">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="company-info">
                @if ($empresa->logo)
                    <img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo" class="company-logo">
                @endif
                <div class="company-details">
                    <h1>{{ $empresa->nombre_comercial }}</h1>
                    <p><i class="bx bx-map"></i> {{ $empresa->direccion_fiscal }}</p>
                    <p><i class="bx bx-phone"></i> {{ $empresa->phone ?? $empresa->rep_phone }}</p>
                </div>
            </div>
            <div class="quick-actions">
                <a href="{{ route('pos.index') }}" class="quick-action-btn primary">
                    <i class="bx bx-cart"></i> Punto de Venta
                </a>
                <a href="{{ route('reportes.index') }}" class="quick-action-btn secondary">
                    <i class="bx bx-bar-chart-alt-2"></i> Reportes
                </a>
                <a href="{{ route('almacen.index') }}" class="quick-action-btn secondary">
                    <i class="bx bx-package"></i> Productos
                </a>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-card blue">
                <div class="icon"><i class="bx bx-dollar-circle"></i></div>
                <div class="label">Venta de Hoy</div>
                <div class="value">S/ {{ number_format($chartData['ventaHoy'], 2) }}</div>
                @php
                    $diff = $chartData['ventaHoy'] - $chartData['ventaAyer'];
                    $pct = $chartData['ventaAyer'] > 0 ? round(($diff / $chartData['ventaAyer']) * 100, 1) : 0;
                @endphp
                <div class="comparison {{ $diff >= 0 ? 'up' : 'down' }}">
                    <i class="bx bx-{{ $diff >= 0 ? 'up' : 'down' }}-arrow-alt"></i>
                    {{ abs($pct) }}% vs ayer
                </div>
            </div>

            <div class="stat-card green">
                <div class="icon"><i class="bx bx-trending-up"></i></div>
                <div class="label">Venta Promedio por Día</div>
                <div class="value">S/ {{ number_format($chartData['ventaPromedioDiaria'], 2) }}</div>
                <div class="comparison" style="color:#94a3b8; font-size:0.75rem;">
                    Basado en el mes en curso
                </div>
            </div>

            <div class="stat-card purple">
                <div class="icon"><i class="bx bx-calendar-check"></i></div>
                <div class="label">Venta del Mes</div>
                <div class="value">S/ {{ number_format($chartData['ventaMes'], 2) }}</div>
                @php
                    $diffMes = $chartData['ventaMes'] - $chartData['ventaMesPasado'];
                    $pctMes = $chartData['ventaMesPasado'] > 0 ? round(($diffMes / $chartData['ventaMesPasado']) * 100, 1) : 0;
                @endphp
                <div class="comparison {{ $diffMes >= 0 ? 'up' : 'down' }}">
                    <i class="bx bx-{{ $diffMes >= 0 ? 'up' : 'down' }}-arrow-alt"></i>
                    {{ abs($pctMes) }}% vs mes pasado
                </div>
            </div>

            <div class="stat-card orange">
                <div class="icon"><i class="bx bx-calendar-minus"></i></div>
                <div class="label">Mes Pasado</div>
                <div class="value">S/ {{ number_format($chartData['ventaMesPasado'], 2) }}</div>
                <div class="comparison" style="color:#94a3b8; font-size:0.75rem;">
                    {{ \Carbon\Carbon::now()->subMonth()->translatedFormat('F Y') }}
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <div class="chart-card">
                <h3><i class="bx bx-line-chart"></i> Ventas de los Últimos 6 Meses</h3>
                <canvas id="salesChart" height="120"></canvas>
            </div>

            <div class="chart-card">
                <h3><i class="bx bx-pie-chart-alt-2"></i> Ventas por Tipo</h3>
                <canvas id="typeChart" height="200"></canvas>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <h3><i class="bx bx-bar-chart"></i> Ventas Últimos 7 Días</h3>
                <canvas id="weekChart" height="100"></canvas>
            </div>

            <div class="chart-card">
                <h3><i class="bx bx-trophy"></i> Top Productos (30 días)</h3>
                <table class="products-table">
                    @forelse($chartData['topProductos'] as $index => $producto)
                        <tr>
                            <td
                                class="rank {{ $index === 0 ? 'gold' : ($index === 1 ? 'silver' : ($index === 2 ? 'bronze' : 'normal')) }}">
                                {{ $index + 1 }}
                            </td>
                            <td class="name">{{ $producto->nombre }}</td>
                            <td class="qty">{{ number_format($producto->cantidad, 0) }} uds</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Sin datos</td>
                        </tr>
                    @endforelse
                </table>
            </div>
        </div>

        <!-- Bottom Info Cards -->
        <div class="bottom-grid">
            <div class="info-card">
                <h4><i class="bx bx-shopping-bag"></i> Ventas & Pedidos</h4>
                <div class="info-list">
                    <div class="info-item">
                        <span class="key">Proformas pendientes</span>
                        <span class="value">{{ $proformas_pendientes_cnt }} <small>(S/
                                {{ number_format($proformas_pendientes_monto, 2) }})</small></span>
                    </div>
                    <div class="info-item">
                        <span class="key">Créditos pendientes</span>
                        <span class="value warning">{{ $creditos_pendientes_cnt }} <small>(S/
                                {{ number_format($creditos_pendientes_monto, 2) }})</small></span>
                    </div>
                    <div class="info-item">
                        <span class="key">Cobros vencidos</span>
                        <span class="value danger">{{ $cobros_vencidos_cnt }} <small>(S/
                                {{ number_format($cobros_vencidos_monto, 2) }})</small></span>
                    </div>
                </div>
            </div>

            <div class="info-card">
                <h4><i class="bx bx-package"></i> Almacén</h4>
                <div class="info-list">
                    <div class="info-item">
                        <span class="key">Productos con stock</span>
                        <span class="value success">{{ $productos_stock_cnt }}</span>
                    </div>
                    <div class="info-item">
                        <span class="key">Productos sin stock</span>
                        <span class="value danger">{{ $productos_sin_stock_cnt }}</span>
                    </div>
                    @if ($productos_stock_minimo_cnt > 0)
                        <div class="info-item"
                            style="background: linear-gradient(90deg, #fef2f2 0%, #fff 100%); border-left: 3px solid #ef4444;">
                            <span class="key"><i class="bx bx-error-circle" style="color: #ef4444;"></i> Stock
                                Mínimo</span>
                            <span class="value danger" style="font-weight: 700;">⚠️
                                {{ $productos_stock_minimo_cnt }}</span>
                        </div>
                    @else
                        <div class="info-item">
                            <span class="key">Stock Mínimo</span>
                            <span class="value success">0</span>
                        </div>
                    @endif
                    <div class="info-item">
                        <span class="key">Compras pendientes</span>
                        <span class="value warning">{{ $comprobantes_pendientes_cnt }}</span>
                    </div>
                </div>
            </div>

            <div class="info-card">
                <h4><i class="bx bx-money"></i> Capital Actual</h4>
                <div class="info-list">
                    <div class="info-item">
                        <span class="key">Total Costo</span>
                        <span class="value">S/ {{ number_format($capital_costo, 2) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="key">Total Precio Venta</span>
                        <span class="value">S/ {{ number_format($capital_venta, 2) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="key">Margen Utilidad</span>
                        <span class="value success">S/ {{ number_format($capital_utilidad, 2) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="key">IGV Estimado</span>
                        <span class="value">S/ {{ number_format($capital_impuesto, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = @json($chartData);

            // Sales Chart (Line)
            new Chart(document.getElementById('salesChart'), {
                type: 'line',
                data: {
                    labels: chartData.mesesLabels,
                    datasets: [{
                        label: 'Ventas (S/)',
                        data: chartData.ventasData,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            },
                            ticks: {
                                callback: function(value) {
                                    return 'S/ ' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Type Chart (Doughnut)
            const tiposLabels = chartData.ventasPorTipo.map(t => t.tipo);
            const tiposData = chartData.ventasPorTipo.map(t => t.total);

            new Chart(document.getElementById('typeChart'), {
                type: 'doughnut',
                data: {
                    labels: tiposLabels,
                    datasets: [{
                        data: tiposData,
                        backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20
                            }
                        }
                    }
                }
            });

            // Week Chart (Bar)
            new Chart(document.getElementById('weekChart'), {
                type: 'bar',
                data: {
                    labels: chartData.diasLabels,
                    datasets: [{
                        label: 'Ventas (S/)',
                        data: chartData.ventasDiarias,
                        backgroundColor: 'rgba(99, 102, 241, 0.8)',
                        borderRadius: 8,
                        barThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            },
                            ticks: {
                                callback: function(value) {
                                    return 'S/ ' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
