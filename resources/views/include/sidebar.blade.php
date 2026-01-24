<nav id="layout-menu" class="layout-menu-horizontal menu-horizontal menu bg-white border-bottom">
    <div class="container-xxl d-flex align-items-center justify-content-between w-100">
        
        <div class="d-flex align-items-center">
            <a href="{{ route('principal.index') }}" class="app-brand-link me-3">
                <span class="app-brand-text demo menu-text fw-bolder" 
                      style="color: #566a7f; font-size: 1.2rem;">Wolvix</span>
            </a>
        </div>

        <ul class="menu-inner list-unstyled m-0 flex-grow-1">
            <li class="menu-item {{ request()->is('principal*') ? 'active' : '' }}">
                <a href="{{ route('principal.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-circle"></i>
                    <div>Dashboard</div>
                </a>
            </li>

            <li class="menu-item {{ request()->is('compras*') ? 'active' : '' }}">
                <a href="{{ route('compras.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-cart"></i>
                    <div>Compras</div>
                </a>
            </li>

            <li class="menu-item {{ request()->is('almacen*') ? 'active' : '' }}">
                <a href="{{ route('almacen.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-package"></i>
                    <div>Almacén</div>
                </a>
            </li>

            <li class="menu-item {{ request()->is('pos*') ? 'active' : '' }}">
                <a href="{{ route('pos.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-collection"></i>
                    <div>Ventas</div>
                </a>
            </li>

            <li class="menu-item {{ request()->is('cotizaciones*') ? 'active' : '' }}">
                <a href="{{ route('cotizaciones.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file"></i>
                    <div>Cotizaciones</div>
                </a>
            </li>

            <li class="menu-item {{ request()->is('deudas*') ? 'active' : '' }}">
                <a href="{{ route('deudas.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-receipt"></i>
                    <div>Deudas</div>
                </a>
            </li>

            <li class="menu-item {{ request()->is('tesoreria*') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-wallet"></i>
                    <div>Tesorería</div>
                </a>
            </li>

            <li class="menu-item {{ request()->is('documentos*') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-cloud-upload"></i>
                    <div>Doc. Electrónico</div>
                </a>
            </li>

            <li class="menu-item {{ request()->is('reportes*') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                    <div>Reportes</div>
                </a>
            </li>
        </ul>

        <div class="navbar-nav-admin ms-2">
            <span class="text-muted small fw-light" style="white-space: nowrap;">Admin Wolvix</span>
        </div>
    </div>
</nav>