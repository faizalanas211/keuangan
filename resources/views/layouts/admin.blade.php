<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
    data-assets-path="../assets/" data-template="vertical-menu-template-free">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Sistem Inventaris</title>

    <meta name="description" content="" />
    @include('includes.style')
    @stack('css')
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->

            @include('includes.sidebar')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                @include('includes.navbar')
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">
                        {{-- Breadcrumb --}}
                        @hasSection('breadcrumb')
                            <nav aria-label="breadcrumb" class="mb-3">
                                <ol class="breadcrumb" style="--bs-breadcrumb-divider: '›';">

                                {{-- Icon Home --}}
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                        <i class="bx bx-home fs-10"></i> {{-- fs-6 lebih kecil, fs-5 sedang --}}
                                    </a>
                                </li>


                                {{-- Dynamic Items --}}
                                @yield('breadcrumb')
                                </ol>
                            </nav>
                        @endif

                        @yield('content')
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    @include('includes.footer')
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->
    @include('includes.script')
    @stack('js')
    <style>
        /* backdrop SweetAlert menutupi seluruh layar (navbar + sidebar) */
        .swal2-container {
            z-index: 20000 !important;
        }

        .swal2-backdrop-show {
            background: rgba(0, 0, 0, 0.35) !important;
            backdrop-filter: blur(2px);
        }
    </style>

</body>

</html>
