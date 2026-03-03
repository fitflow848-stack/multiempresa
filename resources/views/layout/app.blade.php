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
    {{-- <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script> --}}
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>


    <script>
        document.addEventListener('keydown', function(event) {
            // Atajo F2 para ingresar a Caja
            if (event.key === 'F2') {
                event.preventDefault();
                window.location.href = "{{ route('cierre-caja.index') }}";
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
