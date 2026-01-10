@php
    // Detectar el módulo actual basado en la ruta
    $currentRoute = request()->route()->getName() ?? '';
    $currentUrl = request()->url();

    // Configuración de módulos
    $modules = [
        'almacen' => [
            'name' => 'Inventario Stock Almacén',
            'icon' => 'fas fa-warehouse',
            'class' => 'almacen',
        ],
        'compras' => [
            'name' => 'Gestión de Compras',
            'icon' => 'fas fa-shopping-cart',
            'class' => 'compras',
        ],
        'ventas' => [
            'name' => 'Gestión de Ventas',
            'icon' => 'fas fa-cash-register',
            'class' => 'ventas',
        ],
        'productos' => [
            'name' => 'Gestión de Productos',
            'icon' => 'fas fa-boxes',
            'class' => 'productos',
        ],
        'proveedores' => [
            'name' => 'Gestión de Proveedores',
            'icon' => 'fas fa-truck',
            'class' => 'compras',
        ],
        'reportes' => [
            'name' => 'Centro de Reportes',
            'icon' => 'fas fa-chart-bar',
            'class' => 'reportes',
        ],
    ];

    // Detectar módulo actual
    $currentModule = 'default';
    $moduleConfig = [
        'name' => 'Sistema de Gestión',
        'icon' => 'fas fa-home',
        'class' => 'compras',
    ];

    foreach ($modules as $key => $config) {
        if (str_contains($currentRoute, $key) || str_contains($currentUrl, $key)) {
            $currentModule = $key;
            $moduleConfig = $config;
            break;
        }
    }

    // Obtener variables globales si están disponibles
    $company = $company ?? (object) ['nombre_comercial' => 'N/A'];
    $user = auth()->user() ?? (object) ['name' => 'N/A'];
@endphp

<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached bg-navbar-theme" id="layout-navbar">

    <!-- ☰ Toggle menú móvil -->
    <div class="layout-menu-toggle navbar-nav align-items-center me-3 d-xl-none">
        <a class="nav-item nav-link px-0" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <!-- IZQUIERDA -->
    <div class="navbar-nav align-items-center">
        <div>
            <h5 class="mb-0">
                <i class="{{ $moduleConfig['icon'] }} me-2"></i>
                {{ $moduleConfig['name'] }}
            </h5>
            <small class="text-muted">
                <strong>Local:</strong> {{ $company->nombre_comercial ?? 'N/A' }} |
                <strong>Operador:</strong> {{ $user->name ?? 'N/A' }}
            </small>
        </div>
    </div>

    <!-- DERECHA -->
    <ul class="navbar-nav flex-row align-items-center ms-auto">

        <!-- GitHub -->
        <li class="nav-item lh-1 me-3">
            <a class="github-button" href="https://github.com/themeselection/sneat-html-admin-template-free"
                data-icon="octicon-star" data-size="large" data-show-count="true">
                Star
            </a>
        </li>

        <!-- USUARIO -->
        <li class="nav-item dropdown dropdown-user">
            <a class="nav-link dropdown-toggle hide-arrow" href="#" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">

                <div class="avatar avatar-online">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" class="w-px-40 rounded-circle" alt="Usuario">
                </div>
            </a>

            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="#">
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar avatar-online">
                                    <img src="{{ asset('assets/img/avatars/1.png') }}" class="w-px-40 rounded-circle">
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <span class="fw-semibold d-block">{{ $user->name }}</span>
                                <small class="text-muted">Administrador</small>
                            </div>
                        </div>
                    </a>
                </li>

                <li>
                    <div class="dropdown-divider"></div>
                </li>

                <li>
                    <a class="dropdown-item" href="">
                        <i class="bx bx-user me-2"></i> Mi Perfil
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="">
                        <i class="bx bx-cog me-2"></i> Configuración
                    </a>
                </li>

                <li>
                    <div class="dropdown-divider"></div>
                </li>

                <li>
                    <form method="POST" action="">
                        @csrf
                        <button class="dropdown-item text-danger">
                            <i class="bx bx-power-off me-2"></i> Cerrar sesión
                        </button>
                    </form>
                </li>
            </ul>
        </li>

    </ul>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
