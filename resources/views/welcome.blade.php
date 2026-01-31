@extends('layout.app')

@section('content')
    <!-- Dashboard view replicating the provided design -->
    <style>
        /* Basic reset for this section only */
        .acbem-dashboard {
            font-family: "Helvetica Neue", Arial, sans-serif;
            color: #333;
            padding: 40px 60px;
        }

        .acbem-header {
            display: flex;
            align-items: flex-start;
            gap: 30px;
            margin-bottom: 30px;
        }

        .acbem-logo {
            flex: 0 0 360px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .acbem-logo img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .acbem-title {
            font-weight: 900;
            letter-spacing: 2px;
            font-size: 64px;
            margin-top: 6px;
            color: #0b0b0b;
        }

        .acbem-company {
            flex: 1;
            padding-left: 30px;
            border-left: 3px dotted #cfcfcf;
        }

        .acbem-company h1 {
            margin: 0 0 6px;
            font-size: 26px;
            font-weight: 700;
        }

        .acbem-company p {
            margin: 0;
            color: #666;
            line-height: 1.4;
        }

        .acbem-phones {
            margin-top: 14px;
            color: #0d7f82;
            font-weight: 700;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .phone-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #148f95;
            font-weight: 600;
        }

        /* Action buttons */
        .acbem-actions {
            display: flex;
            gap: 22px;
            justify-content: center;
            margin: 30px 0 50px;
        }

        .action-btn {
            background: #108D8D;
            color: #fff;
            padding: 12px 26px;
            border-radius: 8px;
            display: inline-flex;
            gap: 10px;
            align-items: center;
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.06);
            text-decoration: none;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .action-btn svg {
            width: 18px;
            height: 18px;
            fill: #fff;
        }

        /* Stats grid: two rows x three columns */
        .acbem-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 36px;
            margin-top: 10px;
        }

        .stat-card {
            background: transparent;
        }

        .stat-card h3 {
            font-size: 15px;
            color: #666;
            letter-spacing: 1px;
            margin-bottom: 14px;
            font-weight: 700;
        }

        .stat-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            color: #666;
            font-size: 14px;
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-right: 8px;
        }

        .stat-key {
            color: #888;
        }

        .stat-value {
            color: #222;
            font-weight: 700;
            min-width: 110px;
            text-align: right;
        }

        /* second row (COMPRAS / ALMACEN / CAPITAL) */
        .bottom-row {
            margin-top: 36px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 36px;
            align-items: start;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .acbem-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .acbem-company {
                border-left: none;
                padding-left: 0;
            }

            .acbem-logo {
                flex: unset;
            }

            .acbem-actions {
                flex-wrap: wrap;
                gap: 12px;
            }

            .acbem-stats,
            .bottom-row {
                grid-template-columns: 1fr;
            }

            .stat-value {
                min-width: 70px;
            }

            .acbem-dashboard {
                padding: 20px;
            }
        }
    </style>

    <div class="acbem-dashboard">
        <!-- Header -->
        <div class="acbem-header">
            <div class="acbem-logo">
                <!-- Replace src with your logo path -->
                <img src="{{ asset('storage/' . $empresa->logo) }}" alt="ACBEM logo">
            </div>

            <div class="acbem-company">
                <h1>{{ $empresa->nombre_comercial }}</h1>
                <p>{{ $empresa->direccion_fiscal }}</p>
                <p>{{ $empresa->department }} - {{ $empresa->province }} - {{ $empresa->district }}</p>

                <div class="acbem-phones">
                    <div class="phone-item"><svg viewBox="0 0 24 24" width="16" height="16">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12c0 4.61 3.13 8.48 7.41 9.66.41.09.86-.05 1.12-.36l1.78-2.12c.25-.3.2-.73-.12-.99L10.6 16.6c-.18-.15-.2-.41-.05-.6l1.26-1.6c.32-.41.88-.54 1.35-.3l2.3 1.18c.37.19.82.07 1.08-.28l1.66-2.07c.25-.31.65-.46 1.04-.39C20.86 9.6 22 7.91 22 6c0-5.52-4.48-10-10-10z" />
                        </svg> Tel.: {{ $empresa->rep_phone }}</div>
                    <div class="phone-item"><svg viewBox="0 0 24 24" width="16" height="16">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12c0 4.61 3.13 8.48 7.41 9.66.41.09.86-.05 1.12-.36l1.78-2.12c.25-.3.2-.73-.12-.99L10.6 16.6c-.18-.15-.2-.41-.05-.6l1.26-1.6c.32-.41.88-.54 1.35-.3l2.3 1.18c.37.19.82.07 1.08-.28l1.66-2.07c.25-.31.65-.46 1.04-.39C20.86 9.6 22 7.91 22 6c0-5.52-4.48-10-10-10z" />
                        </svg> {{ $empresa->phone }}</div>
                </div>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="acbem-actions">
            <a href="{{ route('pos.index') }}" class="action-btn">
                <!-- Icon (simple SVG) -->
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M3 6h18v2H3zM3 12h18v2H3zM3 18h18v2H3z" />
                </svg>
                PUNTO VENTA
            </a>

            <a href="{{ route('reportes.index') }}" class="action-btn">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M3 6h18v2H3zM3 11h18v2H3zM3 16h18v2H3z" />
                </svg>
                REPORTES
            </a>

            <a href="{{ route('almacen.index') }}" class="action-btn">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 2L2 7l10 5 10-5zM2 17l10 5 10-5" />
                </svg>
                PRODUCTOS
            </a>
        </div>

        <!-- Top stats row -->
        <!-- Top stats row -->
        <div class="acbem-stats">
            <div class="stat-card">
                <h3>PEDIDOS VENTAS</h3>
                <div class="stat-list">
                    <div class="stat-row">
                        <div class="stat-key">Preventas pendientes</div>
                        <div class="stat-value">{{ $preventas_pendientes_cnt }} ( S/
                            {{ number_format($preventas_pendientes_monto, 2) }} )</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Proformas pendientes</div>
                        <div class="stat-value">{{ $proformas_pendientes_cnt }} ( S/
                            {{ number_format($proformas_pendientes_monto, 2) }} )</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Reservas pendientes</div>
                        <div class="stat-value">{{ $reservas_pendientes_cnt }} ( S/
                            {{ number_format($reservas_pendientes_monto, 2) }} )</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Reservas por entregar</div>
                        <div class="stat-value">{{ $reservas_entregar_cnt }}</div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <h3>VENTAS</h3>
                <div class="stat-list">
                    <div class="stat-row">
                        <div class="stat-key">Creditos pendientes</div>
                        <div class="stat-value">{{ $creditos_pendientes_cnt }} ( S/
                            {{ number_format($creditos_pendientes_monto, 2) }} )</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Servicios pendientes</div>
                        <div class="stat-value">0 ( S/ 0.00 )</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Promociones</div>
                        <div class="stat-value">0</div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <h3>TESORERÍA</h3>
                <div class="stat-list">
                    <div class="stat-row">
                        <div class="stat-key">Pagos pendientes</div>
                        <div class="stat-value">{{ $pagos_pendientes_cnt }} ( S/
                            {{ number_format($pagos_pendientes_monto, 2) }} )</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Pagos vencidos</div>
                        <div class="stat-value">{{ $pagos_vencidos_cnt }} ( S/
                            {{ number_format($pagos_vencidos_monto, 2) }} )</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Cobros pendientes</div>
                        <div class="stat-value">{{ $cobros_pendientes_cnt }} ( S/
                            {{ number_format($cobros_pendientes_monto, 2) }} )</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Cobros vencidos</div>
                        <div class="stat-value">{{ $cobros_vencidos_cnt }} ( S/
                            {{ number_format($cobros_vencidos_monto, 2) }} )</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom stats row -->
        <div class="bottom-row">
            <div class="stat-card">
                <h3>COMPRAS</h3>
                <div class="stat-list">
                    <div class="stat-row">
                        <div class="stat-key">Comprobantes pendientes</div>
                        <div class="stat-value">{{ $comprobantes_pendientes_cnt }}</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Comprobantes borrador</div>
                        <div class="stat-value">{{ $comprobantes_borrador_cnt }}</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Pedidos borrador</div>
                        <div class="stat-value">0</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Pedidos pendientes</div>
                        <div class="stat-value">0</div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <h3>ALMACEN</h3>
                <div class="stat-list">
                    <div class="stat-row">
                        <div class="stat-key">Productos con Stock</div>
                        <div class="stat-value">{{ $productos_stock_cnt }}</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Productos sin stock</div>
                        <div class="stat-value">{{ $productos_sin_stock_cnt }}</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Productos Stock Minimo</div>
                        <div class="stat-value">{{ $productos_stock_minimo_cnt }}</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Pedidos por recibir</div>
                        <div class="stat-value">0</div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <h3>CAPITAL ACTUAL</h3>
                <div class="stat-list">
                    <div class="stat-row">
                        <div class="stat-key">Total Costo</div>
                        <div class="stat-value">S/ {{ number_format($capital_costo, 2) }}</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Total Margen Utilidad</div>
                        <div class="stat-value">S/ {{ number_format($capital_utilidad, 2) }}</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Total Impuesto</div>
                        <div class="stat-value">S/ {{ number_format($capital_impuesto, 2) }}</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-key">Total Precio Venta</div>
                        <div class="stat-value">S/ {{ number_format($capital_venta, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
