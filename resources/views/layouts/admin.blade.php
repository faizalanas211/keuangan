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

<body class="{{ Auth::user() && Auth::user()->role === 'pegawai' ? 'role-pegawai' : '' }}">
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
                                        <i class="bx bx-home fs-10"></i>
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

        /* ===== WARNA KHUSUS UNTUK ROLE PEGAWAI ===== */
        .role-pegawai .btn-primary,
        .role-pegawai .btn-success,
        .role-pegawai .btn-success:active,
        .role-pegawai .btn-primary:active,
        .role-pegawai button.btn-success {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            border: none !important;
            color: white !important;
        }

        .role-pegawai .btn-primary:hover,
        .role-pegawai .btn-success:hover {
            background: linear-gradient(135deg, #d97706, #b45309) !important;
            transform: translateY(-1px);
        }

        /* ===== TOMBOL NAVIGASI DI CREATE PERJADIN ===== */
        .role-pegawai #nextBtn,
        .role-pegawai #submitBtn,
        .role-pegawai button#nextBtn,
        .role-pegawai button#submitBtn {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            border: none !important;
            color: white !important;
        }

        .role-pegawai #nextBtn:hover,
        .role-pegawai #submitBtn:hover {
            background: linear-gradient(135deg, #d97706, #b45309) !important;
            transform: translateY(-1px);
        }

        /* Tombol Sebelumnya (Secondary) */
        .role-pegawai #prevBtn,
        .role-pegawai .btn-secondary {
            background: #fef3c7 !important;
            color: #b45309 !important;
            border: 1px solid #fde047 !important;
        }

        .role-pegawai #prevBtn:hover,
        .role-pegawai .btn-secondary:hover {
            background: #fde047 !important;
            color: #78350f !important;
        }

        /* ===== OVERRIDE KHUSUS UNTUK BTN-GREEN DI PERJADIN ===== */
        .role-pegawai .btn-green {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.35) !important;
        }

        .role-pegawai .btn-green:hover {
            background: linear-gradient(135deg, #d97706, #b45309) !important;
        }

        /* Step circle indicator */
        .role-pegawai .step-item.active .step-circle,
        .role-pegawai .step-item.completed .step-circle {
            background: #f59e0b !important;
        }

        /* ===== JUDUL DAN LABEL ===== */
        .role-pegawai .section-title {
            border-left-color: #f59e0b !important;
            color: #d97706 !important;
        }

        .role-pegawai .card-header h5,
        .role-pegawai .card-header .card-title,
        .role-pegawai h5.mb-0 {
            color: #d97706 !important;
        }

        .role-pegawai .form-label,
        .role-pegawai label {
            color: #b45309 !important;
            font-weight: 500 !important;
        }

        /* Badge bg-success */
        .role-pegawai .badge.bg-success {
            background-color: #f59e0b !important;
        }

        /* Text success untuk breadcrumb dan lainnya */
        .role-pegawai .text-success,
        .role-pegawai .fw-semibold.text-success {
            color: #d97706 !important;
        }

        /* Tombol outline */
        .role-pegawai .btn-outline-success {
            color: #d97706 !important;
            border-color: #f59e0b !important;
            background: transparent !important;
        }

        .role-pegawai .btn-outline-success:hover {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            color: white !important;
            border-color: transparent !important;
        }

        /* Link biasa */
        .role-pegawai a:not(.btn):not(.dropdown-item):not(.nav-link):not(.text-muted) {
            color: #d97706 !important;
        }

        .role-pegawai a:not(.btn):not(.dropdown-item):not(.nav-link):hover {
            color: #b45309 !important;
        }

        /* Card */
        .role-pegawai .card-header {
            border-bottom: 1px solid rgba(245, 158, 11, 0.2) !important;
        }

        .role-pegawai .card {
            border: 1px solid rgba(245, 158, 11, 0.1) !important;
        }

        /* Total card khusus perjadin */
        .role-pegawai .total-card {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        }

        /* Nav tabs */
        .role-pegawai .nav-tabs .nav-link.active {
            color: #d97706 !important;
            border-bottom-color: #f59e0b !important;
        }

        .role-pegawai .nav-tabs .nav-link:hover {
            color: #b45309 !important;
        }

        /* Accordion */
        .role-pegawai .accordion-button:not(.collapsed) {
            background-color: rgba(245, 158, 11, 0.1) !important;
            color: #d97706 !important;
        }

        .role-pegawai .accordion-button:focus {
            border-color: #fbbf24 !important;
            box-shadow: 0 0 0 0.25rem rgba(245, 158, 11, 0.25) !important;
        }

        /* Form control focus */
        .role-pegawai .form-control:focus,
        .role-pegawai .form-select:focus {
            border-color: #fbbf24 !important;
            box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25) !important;
        }

        /* Pagination */
        .role-pegawai .pagination .page-item.active .page-link {
            background-color: #f59e0b !important;
            border-color: #f59e0b !important;
            color: white !important;
        }

        .role-pegawai .pagination .page-link:hover {
            color: #d97706 !important;
        }

        /* Table */
        .role-pegawai .table thead th {
            background-color: rgba(245, 158, 11, 0.08) !important;
            color: #b45309 !important;
        }

        .role-pegawai .table-hover tbody tr:hover {
            background-color: rgba(245, 158, 11, 0.04) !important;
        }

        /* Dropdown */
        .role-pegawai .dropdown-item:hover {
            background-color: rgba(245, 158, 11, 0.1) !important;
            color: #d97706 !important;
        }

        /* Alert */
        .role-pegawai .alert-success {
            background-color: rgba(245, 158, 11, 0.1) !important;
            border-color: #f59e0b !important;
            color: #d97706 !important;
        }

        .role-pegawai .alert-success .alert-link {
            color: #b45309 !important;
        }

        /* Modal footer buttons */
        .role-pegawai .modal-footer .btn-success {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
        }

        /* Breadcrumb */
        .role-pegawai .breadcrumb .breadcrumb-item.active {
            color: #d97706 !important;
        }

        .role-pegawai .breadcrumb a {
            color: #f59e0b !important;
        }

        .role-pegawai .breadcrumb a:hover {
            color: #d97706 !important;
        }

        /* ===== NAVBAR UNTUK ROLE PEGAWAI ===== */
        .role-pegawai .premium-navbar {
            background: #ffffff;
            box-shadow: 0 12px 32px rgba(245, 158, 11, 0.12) !important;
        }

        .role-pegawai .nav-date,
        .role-pegawai .nav-watermark,
        .role-pegawai .nav-watermark a {
            color: #d97706 !important;
        }

        .role-pegawai .layout-menu-toggle i {
            color: #d97706 !important;
        }

        .role-pegawai .layout-menu-toggle:hover {
            background: #fef3c7 !important;
        }
    </style>

</body>

</html>