<nav class="modern-navbar">
    <div class="navbar-container">
        <!-- Logo/Brand -->
        <div class="navbar-brand">
            <div class="brand-logo">
                <span class="logo-text">gpos</span>
            </div>
        </div>

        <!-- Main Navigation Menu -->
        <div class="navbar-menu">
            <a href="{{ route('compras.index') }}" class="nav-link {{ request()->is('compras*') ? 'active' : '' }}">
                <span class="nav-icon">📦</span>
                Compras
            </a>
            <a href="{{ route('almacen.index') }}" class="nav-link {{ request()->is('almacen*') ? 'active' : '' }}">
                <span class="nav-icon">🏪</span>
                Almacén
            </a>
            <a href="{{ route('pos.index') }}" class="nav-link {{ request()->is('pos*') ? 'active' : '' }}">
                <span class="nav-icon">🛒</span>
                Ventas
            </a>
            <a href="{{ route('comprobantes.index') }}" class="nav-link {{ request()->is('comprobantes*') ? 'active' : '' }}">
                <span class="nav-icon">🧾</span>
                Comprobantes
            </a>
            <a href="#" class="nav-link {{ request()->is('tesoreria*') ? 'active' : '' }}">
                <span class="nav-icon">💰</span>
                Tesorería
            </a>
            <a href="#" class="nav-link {{ request()->is('documentos*') ? 'active' : '' }}">
                <span class="nav-icon">📄</span>
                Doc. Electrónico
            </a>
            <a href="#" class="nav-link {{ request()->is('reportes*') ? 'active' : '' }}">
                <span class="nav-icon">📊</span>
                Reportes
            </a>
        </div>

        <!-- Right Side Menu -->
        <div class="navbar-actions">
            <a href="#" class="action-link" title="Buzón">
                <span class="action-icon">📬</span>
                <span class="action-text">Buzón</span>
            </a>
            <a href="#" class="action-link" title="Configuración">
                <span class="action-icon">⚙️</span>
                <span class="action-text">Configuración</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="action-link logout-btn" title="Cerrar Sesión">
                    <span class="action-icon">🚪</span>
                    <span class="action-text">Salir</span>
                </button>
            </form>
        </div>

        <!-- Mobile Menu Toggle -->
        <div class="mobile-toggle">
            <button class="mobile-btn" id="mobileMenuToggle">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </div>

    <!-- Mobile Menu Dropdown -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-section">
            <div class="mobile-section-title">Módulos</div>
            <a href="#" class="mobile-nav-link">
                <span class="nav-icon">📦</span>
                Compras
            </a>
            <a href="#" class="mobile-nav-link">
                <span class="nav-icon">🏪</span>
                Almacén
            </a>
            <a href="{{ route('pos.index') }}" class="mobile-nav-link">
                <span class="nav-icon">🛒</span>
                Ventas
            </a>
            <a href="#" class="mobile-nav-link">
                <span class="nav-icon">💰</span>
                Tesorería
            </a>
            <a href="#" class="mobile-nav-link">
                <span class="nav-icon">📄</span>
                Doc. Electrónico
            </a>
            <a href="#" class="mobile-nav-link">
                <span class="nav-icon">📊</span>
                Reportes
            </a>
        </div>

        <div class="mobile-menu-section">
            <div class="mobile-section-title">Acciones</div>
            <a href="#" class="mobile-nav-link">
                <span class="action-icon">📬</span>
                Buzón
            </a>
            <a href="#" class="mobile-nav-link">
                <span class="action-icon">⚙️</span>
                Configuración
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="mobile-nav-link logout-mobile">
                    <span class="action-icon">🚪</span>
                    Salir
                </button>
            </form>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileMenu = document.getElementById('mobileMenu');

        if (mobileMenuToggle && mobileMenu) {
            mobileMenuToggle.addEventListener('click', function() {
                mobileMenu.classList.toggle('active');

                // Animate hamburger lines
                const lines = this.querySelectorAll('.hamburger-line');
                if (mobileMenu.classList.contains('active')) {
                    lines[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                    lines[1].style.opacity = '0';
                    lines[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
                } else {
                    lines[0].style.transform = '';
                    lines[1].style.opacity = '1';
                    lines[2].style.transform = '';
                }
            });

            // Close mobile menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!mobileMenuToggle.contains(event.target) && !mobileMenu.contains(event.target)) {
                    mobileMenu.classList.remove('active');
                    const lines = mobileMenuToggle.querySelectorAll('.hamburger-line');
                    lines[0].style.transform = '';
                    lines[1].style.opacity = '1';
                    lines[2].style.transform = '';
                }
            });
        }
    });
</script>
