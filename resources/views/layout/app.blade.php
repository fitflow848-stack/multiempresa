<!DOCTYPE html>
<html lang="es" class="light-style layout-without-menu" dir="ltr" data-theme="theme-default"
    data-assets-path="../assets/" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="" />
    <title>@yield('title')</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/print-js@1.6.0/dist/print.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>
<style>
    /* Fix for layout width and horizontal overflow */
    html,
    body {
        overflow-x: hidden;
        width: 100%;
        margin: 0;
        padding: 0;
    }

    .layout-wrapper,
    .content-wrapper {
        width: 100% !important;
        max-width: none !important;
        flex-basis: auto !important;
    }

    /* Remove any fixed left padding/margin that layout-menu-fixed might have added */
    .layout-page,
    .content-wrapper,
    .layout-wrapper:not(.layout-without-menu) .layout-page {
        padding-left: 0 !important;
        margin-left: 0 !important;
    }

    /* Ensure container-fluid actually takes full width */
    .container-fluid {
        width: 100% !important;
        padding-left: 1.5rem !important;
        padding-right: 1.5rem !important;
    }

    /* Ensure SweetAlert2 appears above Bootstrap modals */
    .swal2-container {
        z-index: 10000 !important;
    }

    .swal2-popup {
        z-index: 10001 !important;
    }

    /* Fix for backdrop */
    .swal2-backdrop-show {
        z-index: 9999 !important;
    }
</style>
@stack('styles')

<body class="bg-body {{ View::hasSection('hideSidebar') ? 'p-0' : '' }}">

    @if (!View::hasSection('hideSidebar'))
        @include('include.sidebar')
    @endif

    <div class="layout-wrapper {{ View::hasSection('hideSidebar') ? 'layout-without-menu' : '' }}">
        <div class="content-wrapper {{ View::hasSection('hideSidebar') ? 'w-100 p-0' : '' }}">
            <div class="{{ View::hasSection('hideSidebar') ? 'min-vh-100' : 'flex-grow-1' }}">
                @yield('content')
            </div>
        </div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    {{-- ===================== SCRIPTS ===================== --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/print-js@1.6.0/dist/print.js"></script>


    <script>
        document.addEventListener('keydown', function(event) {
            // Atajo F2 para ingresar a Caja
            if (event.key === 'F2') {
                event.preventDefault();
                window.location.href = "{{ route('cierre-caja.index') }}";
            }
        });

        // Función global para limpiar el storage del POS
        function clearPOSStorage() {
            const keys = [
                'ventaPersistentePOS',
                'clientePersistentePOS',
                'ticketGuardadoPOS',
                'clienteGuardadoPOS',
                'ticketPOS',
                'clienteSeleccionadoPOS',
                'lotesSeleccionados'
            ];
            keys.forEach(key => {
                localStorage.removeItem(key);
                sessionStorage.removeItem(key);
            });
            console.log('POS Storage cleared');
        }

        // Auto-attach to logout forms
        document.addEventListener('submit', function(e) {
            if (e.target && (e.target.id === 'logout-form' || e.target.action.includes('logout') || e.target.action.includes('select-branch'))) {
                clearPOSStorage();
            }
        });
        // Mostrar mensajes de sesión con SweetAlert2
        @if(session('success'))
            Swal.fire({ icon: 'success', title: '¡Éxito!', text: "{{ session('success') }}", timer: 3000 });
        @endif

        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Error', text: "{{ session('error') }}" });
        @endif

        @if(session('warning'))
            Swal.fire({ icon: 'warning', title: 'Advertencia', text: "{{ session('warning') }}" });
        @endif

        @if(session('info'))
            Swal.fire({ icon: 'info', title: 'Información', text: "{{ session('info') }}" });
        @endif
    </script>
    <script>
        // Doble clic / doble envío: un formulario que ya se está enviando no se vuelve a enviar durante unos segundos
        document.addEventListener('submit', function (e) {
            if (e.defaultPrevented) return; // cancelado por una confirmación u otro script
            const f = e.target;
            if (!(f instanceof HTMLFormElement) || (f.method || '').toLowerCase() !== 'post') return;
            if (f.dataset.enviando === '1') { e.preventDefault(); return; }
            f.dataset.enviando = '1';
            // si algún otro script cancela el envío (validación), el formulario vuelve a quedar disponible
            setTimeout(function () { if (e.defaultPrevented) delete f.dataset.enviando; }, 0);
            setTimeout(function () { delete f.dataset.enviando; }, 8000);
        });
    </script>
    <script>
        // Precarga del PDF a imprimir: al emitir una venta se descarga en segundo plano el ticket que más se usa, mientras
        // el usuario mira el modal de formatos. Al imprimir, Print.js usa ese PDF ya descargado en vez de esperar al servidor.
        window.pdfPrecargado = {};
        window.precargarPDF = function (url) {
            if (!url) return;
            const previo = window.pdfPrecargado[url];
            if (previo && (Date.now() - previo.hora) < 120000) return; // ya hay una precarga reciente
            const promesa = fetch(url, { credentials: 'same-origin' })
                .then(r => r.ok ? r.blob() : Promise.reject(new Error('HTTP ' + r.status)))
                .then(blob => URL.createObjectURL(blob))
                .catch(() => null);
            window.pdfPrecargado[url] = { hora: Date.now(), promesa };
        };
        // Devuelve la dirección con la que se debe imprimir: el PDF precargado si está (y es reciente), o la original
        window.urlPDFParaImprimir = async function (url) {
            const p = window.pdfPrecargado[url];
            if (!p || (Date.now() - p.hora) >= 120000) return url;
            const blobUrl = await p.promesa;
            return blobUrl || url;
        };
    </script>
    <script>
        // Escape cierra el modal que esté abierto (el de más arriba), en todas las pantallas: modales de Bootstrap y los
        // cuadros propios (capas a pantalla completa con un botón de cerrar). Si no hay ningún modal abierto no hace nada,
        // y el resto de atajos (por ejemplo "cancelar venta" del POS) siguen como antes.
        (function () {
            function visible(el) {
                const st = getComputedStyle(el);
                return st.display !== 'none' && st.visibility !== 'hidden' && el.getClientRects().length > 0;
            }
            function esCapaModal(el) {
                if (getComputedStyle(el).position !== 'fixed') return false;
                const r = el.getBoundingClientRect();
                const style = el.getAttribute('style') || '';
                return (r.width >= innerWidth * 0.9 && r.height >= innerHeight * 0.9)
                    || (/width:\s*100%/.test(style) && /height:\s*100%/.test(style));
            }
            function botonCerrar(el) {
                // Un modal puede marcar con data-esc-cerrar el botón que Escape debe usar (si el de la X no hace lo mismo)
                const marcado = el.querySelector('[data-esc-cerrar]');
                if (marcado && visible(marcado)) return marcado;
                const botones = el.querySelectorAll('[data-bs-dismiss="modal"], .btn-close, .modal-close, button[aria-label="Close"], button[aria-label="Cerrar"], [onclick*="cerrar"], [onclick*="Cerrar"], [onclick*="close"]');
                return Array.from(botones).find(visible) || null;
            }
            function modalSuperior() {
                const candidatos = [];
                document.querySelectorAll('.modal.show').forEach(el => candidatos.push({ el, tipo: 'bootstrap' }));
                document.querySelectorAll('[style*="position: fixed"], [style*="position:fixed"], [id^="modal"], [class*="modal-overlay"]').forEach(el => {
                    if (el.classList.contains('modal') || el.classList.contains('modal-backdrop')) return;
                    if (visible(el) && esCapaModal(el)) candidatos.push({ el, tipo: 'capa' });
                });
                candidatos.sort((a, b) => {
                    const za = parseInt(getComputedStyle(a.el).zIndex) || 0, zb = parseInt(getComputedStyle(b.el).zIndex) || 0;
                    if (za !== zb) return za - zb;
                    return (a.el.compareDocumentPosition(b.el) & Node.DOCUMENT_POSITION_FOLLOWING) ? -1 : 1;
                });
                return candidatos.length ? candidatos[candidatos.length - 1] : null;
            }
            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape' || e.defaultPrevented) return;
                if (document.querySelector('.swal2-container')) return; // SweetAlert ya se cierra solo con Escape
                const m = modalSuperior();
                if (!m) return;
                let cerrado = false;
                // Primero se presiona el botón de cerrar del propio modal: así corre su lógica de cierre (por ejemplo, el
                // modal de impresión vacía el carrito y actualiza el stock al cerrarse). Cerrarlo directamente se la saltaba.
                const b = botonCerrar(m.el);
                if (b) {
                    b.click();
                    cerrado = true;
                } else if (m.tipo === 'bootstrap' && window.bootstrap) {
                    const inst = bootstrap.Modal.getInstance(m.el);
                    if (inst) { inst.hide(); cerrado = true; }
                } else if (/^modal/.test(m.el.id)) {
                    m.el.style.display = 'none';
                    cerrado = true;
                }
                if (cerrado) { e.preventDefault(); e.stopImmediatePropagation(); }
            }, true);
        })();
    </script>
    @stack('scripts')
</body>

</html>
