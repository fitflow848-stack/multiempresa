<style>
    /* Remover el CSS problemático y usar mejor approach */
    .navbar-nav {
        gap: 0.25rem;
    }

    .nav-link {
        white-space: nowrap;
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem !important;
    }

    /* Ajustar para pantallas medianas */
    @media (min-width: 1200px) and (max-width: 1400px) {
        .nav-link {
            font-size: 0.85rem;
            padding: 0.5rem 0.6rem !important;
        }

        .nav-link i {
            font-size: 0.9rem;
        }
    }

    /* Para pantallas muy grandes */
    @media (min-width: 1400px) {
        .navbar-nav {
            gap: 0.5rem;
        }
    }

    /* Mobile styles */
    @media (max-width: 1199px) {
        .navbar-nav .nav-link {
            padding: 0.75rem 1rem !important;
            border-bottom: 1px solid #f0f0f0;
        }

        .navbar-nav .nav-link:hover {
            background-color: #f8f9fa;
        }
    }
</style>

<nav class="navbar navbar-expand-xl navbar-light bg-white border-bottom sticky-top">
    <div class="container-fluid px-3 px-xl-4">
        <!-- Logo -->
        <a href="{{ route('principal.index') }}" class="navbar-brand d-flex align-items-center me-2 me-xl-4">
            <span class="fw-bolder" style="color: #566a7f; font-size: 1.2rem;">Wolvix</span>
        </a>

        <!-- Toggler Button -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavigation"
            aria-controls="navbarNavigation" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Collapsible Menu -->
        <div class="collapse navbar-collapse" id="navbarNavigation">
            <ul class="navbar-nav me-auto mb-2 mb-xl-0">
                <li class="nav-item">
                    <a href="{{ route('principal.index') }}"
                        class="nav-link {{ request()->is('principal*') ? 'active fw-bold text-primary' : '' }}">
                        <i class="bx bx-home-circle me-1"></i> Dashboard
                    </a>
                </li>

                @can('compras.ver')
                    <li class="nav-item">
                        <a href="{{ route('compras.index') }}"
                            class="nav-link {{ request()->is('compras*') ? 'active fw-bold text-primary' : '' }}">
                            <i class="bx bx-cart me-1"></i> Compras
                        </a>
                    </li>
                @endcan

                @can('inventario.ver')
                    <li class="nav-item dropdown">
                        <a href="#"
                            class="nav-link dropdown-toggle {{ request()->is('almacen*') ? 'active fw-bold text-primary' : '' }}"
                            id="almacenDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-package me-1"></i> Almacén
                        </a>
                        <ul class="dropdown-menu border-0 shadow-sm" aria-labelledby="almacenDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('almacen.index') }}">
                                    Inventario
                                </a>
                            </li>
                            @can('inventario.kardex')
                                <li>
                                    <a class="dropdown-item" href="{{ route('almacen.kardex') }}">
                                        Kardex / Movimientos
                                    </a>
                                </li>
                            @endcan
                            @can('inventario.transferir')
                                <li>
                                    <a class="dropdown-item" href="{{ route('almacen.transferir') }}">
                                        Transferencias
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                @canany(['pos.ver', 'ventas.ver', 'cotizaciones.ver'])
                    <li class="nav-item dropdown">
                        <a href="#"
                            class="nav-link dropdown-toggle {{ request()->is('pos*') || request()->is('cotizaciones*') ? 'active fw-bold text-primary' : '' }}"
                            id="ventasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-collection me-1"></i> Ventas
                        </a>
                        <ul class="dropdown-menu border-0 shadow-sm" aria-labelledby="ventasDropdown">
                            @can('pos.ver')
                                <li>
                                    <a class="dropdown-item" href="{{ route('pos.index') }}">
                                        Punto de Venta
                                    </a>
                                </li>
                            @endcan
                            @can('cotizaciones.ver')
                                <li>
                                    <a class="dropdown-item" href="{{ route('cotizaciones.index') }}">
                                        Cotizaciones
                                    </a>
                                </li>
                            @endcan
                            @can('ventas.editar')
                                <li>
                                    <a class="dropdown-item" href="{{ route('pos.precios') }}">
                                        Precios
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                @can('deudas.ver')
                    <li class="nav-item">
                        <a href="{{ route('deudas.index') }}"
                            class="nav-link {{ request()->is('deudas*') ? 'active fw-bold text-primary' : '' }}">
                            <i class="bx bx-receipt me-1"></i> Deudas
                        </a>
                    </li>
                @endcan

                @can('caja.ver')
                    <li class="nav-item">
                        <a href="{{ route('cierre-caja.index') }}"
                            class="nav-link {{ request()->is('cierre-caja*') ? 'active fw-bold text-primary' : '' }}">
                            <i class="bx bx-wallet me-1"></i> Tesorería
                        </a>
                    </li>
                @endcan

                @can('guias_remision.ver')
                    <li class="nav-item dropdown">
                        <a href="#"
                            class="nav-link dropdown-toggle {{ request()->is('guia*') ? 'active fw-bold text-primary' : '' }}"
                            id="docsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-collection me-1"></i> Doc. Electrónico
                        </a>
                        <ul class="dropdown-menu border-0 shadow-sm" aria-labelledby="docsDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('guia.index') }}">
                                    Guías de Remisión
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan



                @can('contabilidad.ver')
                    <li class="nav-item dropdown">
                        <a href="#"
                            class="nav-link dropdown-toggle {{ request()->is('balance*') || request()->is('activos*') || request()->is('pasivos*') ? 'active fw-bold text-primary' : '' }}"
                            id="balanceDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-spreadsheet me-1"></i> Balance
                        </a>
                        <ul class="dropdown-menu border-0 shadow-sm" aria-labelledby="balanceDropdown">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('balance.index') ? 'active' : '' }}"
                                    href="{{ route('balance.index') }}">
                                    Balance General
                                </a>
                            </li>
                            @can('contabilidad.gestionar_activos')
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('activos_corrientes.index') ? 'active' : '' }}"
                                        href="{{ route('activos_corrientes.index') }}">
                                        Activos Corrientes
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('activos.index') ? 'active' : '' }}"
                                        href="{{ route('activos.index') }}">
                                        Activos No Corrientes
                                    </a>
                                </li>
                            @endcan
                            @can('contabilidad.gestionar_pasivos')
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('pasivos.index') ? 'active' : '' }}"
                                        href="{{ route('pasivos.index') }}">
                                        Pasivos Corrientes
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                @can('reportes.ver')
                    <li class="nav-item">
                        <a href="{{ route('reportes.index') }}"
                            class="nav-link {{ request()->is('reportes*') ? 'active fw-bold text-primary' : '' }}">
                            <i class="bx bx-bar-chart-alt-2 me-1"></i> Reportes
                        </a>
                    </li>
                @endcan
            </ul>

            <!-- User Menu -->
            <ul class="navbar-nav ms-auto align-items-xl-center mt-3 mt-xl-0">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="d-flex flex-column text-end me-2">
                                <span class="fw-bold small lh-1">{{ Auth::user()->name }}</span>
                                <small class="text-muted" style="font-size: 0.7rem;">Administrador</small>
                            </div>
                            <i class="bx bx-user-circle fs-3"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('principal.index') }}">
                                    <i class="bx bx-user me-2"></i> Mi Perfil
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bx bx-log-out me-2"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
