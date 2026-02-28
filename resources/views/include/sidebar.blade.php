<style>
    /* Estilos base del Navbar */
    .navbar {
        z-index: 1070 !important;
    }

    .dropdown-menu {
        z-index: 1080 !important;
    }

    .navbar-nav {
        gap: 0.15rem;
    }

    .nav-link {
        white-space: nowrap;
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem !important;
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

    /* Estilos para el modo oculto / slim */
    .navbar-hidden {
        display: none !important;
    }

    .navbar-slim {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        min-height: auto !important;
    }

    .navbar-slim .navbar-brand {
        margin-right: 1rem !important;
    }

    .navbar-slim .navbar-brand span {
        font-size: 1rem !important;
    }

    .navbar-slim .nav-link {
        padding: 0.3rem 0.5rem !important;
        font-size: 0.85rem !important;
    }

    /* Activador flotante cuando está oculto */
    #navbar-restore-trigger {
        position: fixed;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2000;
        background: #566a7f;
        color: white;
        padding: 0 20px;
        border-radius: 0 0 10px 10px;
        cursor: pointer;
        display: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        opacity: 0.6;
        transition: all 0.2s;
        height: 12px;
        align-items: center;
        justify-content: center;
    }

    #navbar-restore-trigger:hover {
        opacity: 1;
        height: 25px;
    }

    body.nav-is-hidden #navbar-restore-trigger {
        display: flex;
    }

    #btn-sidebar-toggle i {
        transition: transform 0.3s;
    }

    @media (max-width: 1199px) {
        #btn-sidebar-toggle, #navbar-restore-trigger {
            display: none !important;
        }
    }
</style>

<script>
    // Aplicar estado inicial antes de que se cargue el DOM para evitar parpadeo
    (function() {
        const isHidden = localStorage.getItem('navbarHidden') === 'true';
        if (isHidden) {
            document.documentElement.classList.add('nav-is-hidden');
        }
    })();
</script>

<!-- Activador flotante -->
<div id="navbar-restore-trigger" onclick="toggleNavbarVisibility()" title="Mostrar Menú Principal">
    <i class="bx bx-chevron-down"></i>
</div>

<nav id="main-navbar" class="navbar navbar-expand-xl navbar-light bg-white border-bottom sticky-top">
    <div class="container-fluid px-3 px-xl-4">
        <!-- Logo -->
        <div class="d-flex align-items-center me-2 me-xl-4 text-nowrap">
            <button class="btn btn-icon btn-sm btn-outline-secondary me-2 d-none d-xl-inline-flex" 
                id="btn-sidebar-toggle" onclick="toggleNavbarVisibility()" title="Ocultar Menú">
                <i class="bx bx-chevron-up"></i>
            </button>
            <a href="{{ route('principal.index') }}" class="navbar-brand d-flex align-items-center">
                <span class="fw-bolder" style="color: #566a7f; font-size: 1.2rem;">Wolvix</span>
            </a>
        </div>

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
                        <i class="bx bx-home-circle me-1"></i> Inicio
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
                            @if (isset($current_user_cajas) && $current_user_cajas->count() > 0)
                                <li class="dropdown-header text-uppercase fs-tiny fw-bold">Caja Activa</li>
                                @foreach ($current_user_cajas as $caja)
                                    @php
                                        $sesionAbierta = \App\Models\CierreCaja::where('caja_id', $caja->id)
                                            ->whereNull('fecha_cierre')
                                            ->first();
                                        $enUsoPorOtro = $sesionAbierta && $sesionAbierta->user_id !== auth()->id();
                                    @endphp
                                    <li>
                                        <form action="{{ route('caja.select') }}" method="POST"
                                            id="form-caja-{{ $caja->id }}">
                                            @csrf
                                            <input type="hidden" name="caja_id" value="{{ $caja->id }}">
                                            <button type="submit"
                                                {{ $enUsoPorOtro ? 'disabled' : '' }}
                                                class="dropdown-item d-flex justify-content-between align-items-center {{ session('selected_caja_id') == $caja->id ? 'bg-light fw-bold text-primary' : '' }}">
                                                <span class="{{ $enUsoPorOtro ? 'text-muted' : '' }}">
                                                    <i class="bx bx-box me-2"></i>{{ $caja->nombre }}
                                                    @if ($sesionAbierta)
                                                        <small class="ms-1 {{ $enUsoPorOtro ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                                                            ({{ $enUsoPorOtro ? 'En uso: ' . $sesionAbierta->user->name : 'Abierta por ti' }})
                                                        </small>
                                                    @endif
                                                </span>
                                                @if (session('selected_caja_id') == $caja->id)
                                                    <i class="bx bx-check text-primary"></i>
                                                @endif
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                            @endif

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

                @hasanyrole('vendedor|admin_empresa|super_admin')
                    <li class="nav-item dropdown">
                        <a href="#"
                            class="nav-link dropdown-toggle {{ request()->routeIs('finanzas_vendedor.*') ? 'active fw-bold text-primary' : '' }}"
                            id="finanzasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-dollar-circle me-1"></i> Finanzas
                        </a>
                        <ul class="dropdown-menu border-0 shadow-sm" aria-labelledby="finanzasDropdown">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('finanzas_vendedor.*') && request()->get('tipo') == 'adelanto_personal' ? 'active' : '' }}"
                                    href="{{ route('finanzas_vendedor.index', ['tipo' => 'adelanto_personal']) }}">
                                    <i class="bx bx-user me-2"></i> Adelantos a Personal
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('finanzas_vendedor.*') && request()->get('tipo') == 'compras_credito' ? 'active' : '' }}"
                                    href="{{ route('finanzas_vendedor.index', ['tipo' => 'compras_credito']) }}">
                                    <i class="bx bx-cart me-2"></i> Compras a Crédito
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('finanzas_vendedor.*') && request()->get('tipo') == 'adelanto_clientes' ? 'active' : '' }}"
                                    href="{{ route('finanzas_vendedor.index', ['tipo' => 'adelanto_clientes']) }}">
                                    <i class="bx bx-money me-2"></i> Adelanto de Clientes
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('finanzas_vendedor.*') && !request()->get('tipo') ? 'active' : '' }}"
                                    href="{{ route('finanzas_vendedor.index') }}">
                                    <i class="bx bx-list-ul me-2"></i> Ver Todo
                                </a>
                            </li>
                        </ul>
                    </li>
                @endhasanyrole

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
                            <i class="bx bx-collection me-1"></i> Documentos
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
                    @php
                        $selectedCajaBadge = isset($current_user_cajas)
                            ? $current_user_cajas->firstWhere('id', session('selected_caja_id'))
                            : null;

                        $roleName = Auth::user()->getRoleNames()->first() ?? 'Usuario';
                        $roleLabel = match ($roleName) {
                            'super_admin' => 'Super Admin',
                            'admin_empresa' => 'Administrador',
                            'supervisor' => 'Supervisor',
                            'vendedor' => 'Vendedor',
                            default => ucfirst(str_replace('_', ' ', $roleName)),
                        };
                    @endphp
                    @if (isset($current_branch) && $current_branch)
                        <li class="nav-item me-2 d-none d-xl-block">
                            <span class="badge bg-label-secondary shadow-sm px-3 py-2">
                                <i class="bx bx-map-pin me-1"></i> SUCT: {{ $current_branch->nombre }}
                            </span>
                        </li>
                    @endif

                    @if ($selectedCajaBadge)
                        <li class="nav-item me-3 d-none d-xl-block">
                            <a href="{{ route('cierre-caja.index') }}" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center">
                                <i class="bx bx-box me-1"></i> CAJA (F2)
                            </a>
                        </li>
                    @endif
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="d-flex flex-column text-end me-2">
                                <span class="fw-bold small lh-1">{{ Auth::user()->name }}</span>
                                <small class="text-muted" style="font-size: 0.65rem;">{{ $roleLabel }}</small>
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

<script>
    function toggleNavbarVisibility() {
        const navbar = document.getElementById('main-navbar');
        const body = document.body;
        const isHidden = !navbar.classList.contains('navbar-hidden');
        
        if (isHidden) {
            navbar.classList.add('navbar-hidden');
            body.classList.add('nav-is-hidden');
            document.documentElement.classList.add('nav-is-hidden');
        } else {
            navbar.classList.remove('navbar-hidden');
            body.classList.remove('nav-is-hidden');
            document.documentElement.classList.remove('nav-is-hidden');
        }
        
        localStorage.setItem('navbarHidden', isHidden);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.getElementById('main-navbar');
        const body = document.body;
        
        // Cargar estado inicial
        if (localStorage.getItem('navbarHidden') === 'true') {
            navbar.classList.add('navbar-hidden');
            body.classList.add('nav-is-hidden');
            document.documentElement.classList.add('nav-is-hidden');
        }
    });
</script>
