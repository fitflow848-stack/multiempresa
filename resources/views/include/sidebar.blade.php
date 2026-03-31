<style>
    /* Estilos Premium del Navbar */
    #main-navbar {
        background: #ffffff !important;
        border-bottom: 1px solid #eef2f7 !important;
        box-shadow: 0 1px 15px rgba(0, 0, 0, 0.04);
        padding: 0.5rem 0;
        z-index: 1070 !important;
    }

    .navbar-brand span {
        background: linear-gradient(135deg, #566a7f 0%, #3f4e5e 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 800 !important;
        letter-spacing: -0.5px;
    }

    .navbar-nav .nav-link {
        font-size: 0.82rem;
        /* Fuente un poco más pequeña para ganar espacio */
        font-weight: 500;
        color: #64748b !important;
        padding: 0.5rem 0.6rem !important;
        /* Menos padding lateral */
        border-radius: 8px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        white-space: nowrap;
        margin: 0 1px;
    }

    .navbar-nav .nav-link i {
        font-size: 1.1rem;
        margin-right: 4px;
        color: #8e9bac;
    }

    .navbar-nav .nav-link:hover {
        background: #f8f9fa;
        color: #696cff !important;
    }

    .navbar-nav .nav-link:hover i {
        color: #696cff;
        transform: translateY(-1px);
    }

    .navbar-nav .nav-link.active {
        background: #f0f1ff;
        color: #696cff !important;
        font-weight: 600;
    }

    .navbar-nav .nav-link.active i {
        color: #696cff;
    }

    /* Mejora del dropdown */
    .dropdown-menu {
        border: none !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        border-radius: 12px !important;
        padding: 0.5rem !important;
        z-index: 1080 !important;
    }

    .dropdown-item {
        border-radius: 8px !important;
        padding: 0.6rem 1rem !important;
        font-size: 0.85rem !important;
    }

    /* Mobile adjustments */
    @media (max-width: 991px) {
        .navbar-nav .nav-link {
            padding: 0.8rem 1rem !important;
            margin: 4px 0;
            border-bottom: none;
        }

        .navbar-collapse {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            margin-top: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
    }

    /* Estilos para el modo oculto */
    .navbar-hidden {
        display: none !important;
    }

    /* Activador flotante */
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
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
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

<nav id="main-navbar" class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Logo -->
        <div class="d-flex align-items-center me-2 me-lg-4 text-nowrap">
            <button class="btn btn-icon btn-sm btn-outline-secondary me-2 d-none d-lg-inline-flex" id="btn-sidebar-toggle"
                onclick="toggleNavbarVisibility()" title="Ocultar Menú">
                <i class="bx bx-chevron-up"></i>
            </button>
            <a href="{{ route('principal.index') }}" class="navbar-brand d-flex align-items-center">
                <span class="fw-bolder" style="color: #566a7f; font-size: 1.2rem;">Wolvix</span>
                @if (isset($current_branch) && $current_branch)
                    <span class="ms-2 d-lg-none badge bg-label-primary border-0 shadow-none px-2 py-1"
                        style="font-size: 0.6rem; font-weight: 600; text-transform: uppercase;">
                        <i class="bx bx-map-pin" style="font-size: 0.6rem;"></i>
                        {{ Str::limit($current_branch->nombre, 15) }}
                    </span>
                @endif
            </a>
        </div>

        <!-- Toggler Button -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNavigation" aria-controls="navbarNavigation" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Collapsible Menu -->
        <div class="collapse navbar-collapse" id="navbarNavigation">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @can('compras.ver')
                    <li class="nav-item">
                        <a href="{{ route('compras.index') }}"
                            class="nav-link {{ request()->is('compras*') ? 'active' : '' }}">
                            <i class="bx bx-cart"></i> Compras
                        </a>
                    </li>
                @endcan

                @can('inventario.ver')
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle {{ request()->is('almacen*') ? 'active' : '' }}"
                            id="almacenDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-package"></i> Almacén
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

                @canany(['ventas.pos', 'ventas.ver', 'cotizaciones.ver'])
                    <li class="nav-item dropdown">
                        <a href="#"
                            class="nav-link dropdown-toggle {{ request()->is('pos*') || request()->is('cotizaciones*') ? 'active' : '' }}"
                            id="ventasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-store-alt"></i> Ventas
                        </a>
                        <ul class="dropdown-menu border-0 shadow-sm" aria-labelledby="ventasDropdown">
                            @if (isset($current_user_cajas) && $current_user_cajas->count() > 0)
                                <li class="dropdown-header text-uppercase fs-tiny fw-bold">Cajas de Venta</li>
                                @foreach ($current_user_cajas->where('is_boveda', false) as $caja)
                                    @php
                                        $sesionAbiertaRaw = \Illuminate\Support\Facades\DB::table('cierre_cajas')
                                            ->where('caja_id', $caja->id)
                                            ->whereNull('fecha_cierre')
                                            ->first();
                                        $isAdminRole =
                                            auth()->user()->hasRole('super_admin') ||
                                            auth()->user()->hasRole('admin_empresa');
                                        $enUsoPorOtro =
                                            $sesionAbiertaRaw &&
                                            $sesionAbiertaRaw->user_id !== auth()->id() &&
                                            !$isAdminRole;
                                        $displayEnUso =
                                            $sesionAbiertaRaw && $sesionAbiertaRaw->user_id !== auth()->id();
                                        $nombreEnUso = $displayEnUso
                                            ? optional(\App\Models\User::find($sesionAbiertaRaw->user_id))->name
                                            : null;
                                    @endphp
                                    <li>
                                        <form action="{{ route('caja.select') }}" method="POST"
                                            id="form-caja-{{ $caja->id }}">
                                            @csrf
                                            <input type="hidden" name="caja_id" value="{{ $caja->id }}">
                                            <button type="submit" @if ($enUsoPorOtro) disabled @endif
                                                class="dropdown-item d-flex justify-content-between align-items-center {{ session('selected_caja_id') == $caja->id ? 'bg-light fw-bold text-primary' : '' }}">
                                                <span class="{{ $displayEnUso ? 'text-muted' : '' }}">
                                                    <i class="bx bx-box me-2"></i>{{ $caja->nombre }}
                                                    @if ($sesionAbiertaRaw)
                                                        <small
                                                            class="ms-1 {{ $displayEnUso ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                                                            ({{ $displayEnUso ? 'En uso: ' . $nombreEnUso : 'Abierta por ti' }})
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

                                @if ($current_user_cajas->where('is_boveda', true)->count() > 0)
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li class="dropdown-header text-uppercase fs-tiny fw-bold">Bóvedas / Tesorería</li>
                                    @foreach ($current_user_cajas->where('is_boveda', true) as $caja)
                                        @php
                                            $sesionAbierta = \App\Models\CierreCaja::where('caja_id', $caja->id)
                                                ->whereNull('fecha_cierre')
                                                ->first();
                                            $isAllowedOverride =
                                                auth()->user()->hasRole('super_admin') ||
                                                auth()->user()->hasRole('admin_empresa');
                                            $enUsoPorOtro =
                                                $sesionAbierta &&
                                                $sesionAbierta->user_id !== auth()->id() &&
                                                !$isAllowedOverride;
                                            $displayEnUso = $sesionAbierta && $sesionAbierta->user_id !== auth()->id();
                                        @endphp
                                        <li>
                                            <form action="{{ route('caja.select') }}" method="POST"
                                                id="form-caja-{{ $caja->id }}">
                                                @csrf
                                                <input type="hidden" name="caja_id" value="{{ $caja->id }}">
                                                <button type="submit" {{ $enUsoPorOtro ? 'disabled' : '' }}
                                                    class="dropdown-item d-flex justify-content-between align-items-center {{ session('selected_caja_id') == $caja->id ? 'bg-light fw-bold text-primary' : '' }}">
                                                    <span class="{{ $displayEnUso ? 'text-muted' : '' }}">
                                                        <i class="bx bx-cabinet me-2"></i>{{ $caja->nombre }}
                                                        @if ($sesionAbierta)
                                                            <small
                                                                class="ms-1 {{ $displayEnUso ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                                                                ({{ $displayEnUso ? 'En uso: ' . $sesionAbierta->user->name : 'Abierta por ti' }})
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
                                @endif

                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                            @endif

                            @can('ventas.pos')
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

                @if(auth()->user()->hasAnyRole(['vendedor', 'admin_empresa', 'super_admin']) || auth()->user()->canAny(['deudas.ver', 'cajas.ver', 'tesoreria.ver']))
                    <li class="nav-item dropdown">
                        <a href="#"
                            class="nav-link dropdown-toggle {{ request()->routeIs('finanzas_vendedor.*') || request()->is('deudas*') || request()->is('cierre-caja*') ? 'active' : '' }}"
                            id="finanzasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-wallet"></i> Finanzas
                        </a>
                        <ul class="dropdown-menu border-0 shadow-sm" aria-labelledby="finanzasDropdown">
                            @can('deudas.ver')
                                <li>
                                    <a class="dropdown-item {{ request()->is('deudas*') ? 'active' : '' }}"
                                        href="{{ route('deudas.index') }}">
                                        <i class="bx bx-credit-card-front me-2"></i> Gestión de Deudas
                                    </a>
                                </li>
                            @endcan
                            @can('cajas.ver')
                                <li>
                                    <a class="dropdown-item {{ request()->is('cierre-caja*') && request()->get('tipo') == 'tesoreria' ? 'active' : '' }}"
                                        href="{{ route('cierre-caja.index', ['tipo' => 'tesoreria']) }}">
                                        <i class="bx bx-cabinet me-2"></i> Tesorería / Bóvedas
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->is('cierre-caja*') && request()->get('tipo') != 'tesoreria' ? 'active' : '' }}"
                                        href="{{ route('cierre-caja.index') }}">
                                        <i class="bx bx-box me-2"></i> Arqueo de Cajas Base
                                    </a>
                                </li>
                            @endcan
                            <li>
                                <hr class="dropdown-divider">
                            </li>
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
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('finanzas_vendedor.*') && !request()->get('tipo') ? 'active' : '' }}"
                                    href="{{ route('finanzas_vendedor.index') }}">
                                    <i class="bx bx-list-ul me-2"></i> Ver Todo
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @canany(['reportes.ver', 'contabilidad.ver', 'guias_remision.ver', 'comprobantes.ver'])
                    <li class="nav-item dropdown">
                        <a href="#"
                            class="nav-link dropdown-toggle {{ request()->is('reportes*') || request()->is('balance*') || request()->is('guia*') ? 'active' : '' }}"
                            id="gestionDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-grid-alt"></i> Gestión
                        </a>
                        <ul class="dropdown-menu border-0 shadow-sm" aria-labelledby="gestionDropdown">
                            @canany(['guias_remision.ver', 'comprobantes.ver'])
                                <li class="dropdown-header text-uppercase small fw-bold">Documentación</li>
                                @can('comprobantes.ver')
                                    <li>
                                        <a class="dropdown-item {{ request()->is('comprobantes*') ? 'active' : '' }}"
                                            href="{{ route('comprobantes.index') }}">
                                            <i class="bx bx-receipt me-2"></i> Comprobantes
                                        </a>
                                    </li>
                                @endcan
                                @can('guias_remision.ver')
                                    <li>
                                        <a class="dropdown-item {{ request()->is('guia*') ? 'active' : '' }}"
                                            href="{{ route('guia.index') }}">
                                            <i class="bx bx-file me-2"></i> Guías de Remisión
                                        </a>
                                    </li>
                                @endcan
                            @endcanany

                            @can('contabilidad.ver')
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li class="dropdown-header text-uppercase small fw-bold">Contabilidad</li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('balance.index') ? 'active' : '' }}"
                                        href="{{ route('balance.index') }}">
                                        <i class="bx bx-bar-chart-alt-2 me-2"></i> Balance General
                                    </a>
                                </li>
                                @can('contabilidad.gestionar_activos')
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('activos_corrientes.index') ? 'active' : '' }}"
                                            href="{{ route('activos_corrientes.index') }}">
                                            <i class="bx bx-subdirectory-right me-2 text-muted"></i> Activos Corrientes
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('activos.index') ? 'active' : '' }}"
                                            href="{{ route('activos.index') }}">
                                            <i class="bx bx-subdirectory-right me-2 text-muted"></i> Activos No Corrientes
                                        </a>
                                    </li>
                                @endcan
                                @can('contabilidad.gestionar_pasivos')
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('pasivos.index') ? 'active' : '' }}"
                                            href="{{ route('pasivos.index') }}">
                                            <i class="bx bx-subdirectory-right me-2 text-muted"></i> Pasivos Corrientes
                                        </a>
                                    </li>
                                @endcan
                            @endcan

                            @can('reportes.ver')
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li class="dropdown-header text-uppercase small fw-bold">Análisis</li>
                                <li>
                                    <a class="dropdown-item {{ request()->is('reportes*') ? 'active' : '' }}"
                                        href="{{ route('reportes.index') }}">
                                        <i class="bx bx-pie-chart-alt-2 me-2"></i> Reportes
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan
            </ul>

            <!-- User Menu -->
            <ul class="navbar-nav ms-auto align-items-lg-center mt-3 mt-lg-0">
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
                        <li class="nav-item me-1 d-none d-lg-block">
                            <span class="badge bg-label-secondary border-0 shadow-none px-2 py-1 small"
                                style="font-size: 0.7rem;">
                                <i class="bx bx-map-pin"></i> {{ Str::limit($current_branch->nombre, 10) }}
                            </span>
                        </li>
                    @endif

                    @if ($selectedCajaBadge)
                        <li class="nav-item me-2">
                            <a href="{{ route('cierre-caja.index') }}"
                                class="btn btn-primary btn-xs px-2 shadow-sm py-1 d-flex align-items-center"
                                style="font-size: 0.75rem;">
                                <i class="bx bx-box me-1"></i> CAJA
                            </a>
                        </li>
                    @endif
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center bg-light rounded-pill px-3 ms-2"
                            href="#" id="userDropdown" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <div class="d-flex flex-column text-end me-2">
                                <span class="fw-bold small lh-1 text-dark">{{ Auth::user()->name }}</span>
                                <small class="text-muted" style="font-size: 0.65rem;">{{ $roleLabel }}</small>
                                @if (isset($current_branch) && $current_branch)
                                    <small class="text-primary fw-bold d-lg-none" style="font-size: 0.65rem;">
                                        <i class="bx bx-map-pin" style="font-size: 0.6rem;"></i> {{ $current_branch->nombre }}
                                    </small>
                                @endif
                            </div>
                            <div class="avatar-wrapper bg-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 32px; height: 32px; border: 1px solid #eef2f7;">
                                <i class="bx bx-user text-primary fs-5"></i>
                            </div>
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
