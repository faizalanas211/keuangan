<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    {{-- BRAND --}}
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-text demo menu-text fw-bolder ms-2">
                Slip-Gaji
            </span>
        </a>

        <a href="javascript:void(0);"
           class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">

        {{-- ================= DASHBOARD (SEMUA ROLE) ================= --}}
        <li class="menu-item {{ Request::is('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>Dashboard</div>
            </a>
        </li>

        {{-- ================= ADMIN ================= --}}
        @if (Auth::user()->role === 'admin' || Auth::user()->role === 'Admin')

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Keuangan</span>
            </li>

            <li class="menu-item {{ Request::is('dashboard/penghasilan*') ? 'active' : '' }}">
                <a href="{{ route('penghasilan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-plus-circle"></i>
                    <div>Penghasilan Pegawai</div>
                </a>
            </li>

            <li class="menu-item {{ Request::is('dashboard/potongan*') ? 'active' : '' }}">
                <a href="{{ route('potongan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-minus-circle"></i>
                    <div>Potongan Pegawai</div>
                </a>
            </li>

            <li class="menu-item {{ Request::is('dashboard/slip_gaji*') ? 'active' : '' }}">
                <a href="{{ route('slip-gaji.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-printer"></i>
                    <div>Cetak Slip Gaji</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Master Data</span>
            </li>

            <li class="menu-item {{ Request::is('dashboard/pegawai*') ? 'active' : '' }}">
                <a href="{{ route('pegawai.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user"></i>
                    <div>Data Pegawai</div>
                </a>
            </li>

        @endif

        {{-- ================= PEGAWAI ================= --}}
        @if (Auth::user()->role === 'pegawai')

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Gaji Saya</span>
            </li>

            <li class="menu-item {{ Request::is('dashboard/slip_gaji*') ? 'active' : '' }}">
                <a href="{{ route('slip-gaji.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-receipt"></i>
                    <div>Slip Gaji</div>
                </a>
            </li>

        @endif

    </ul>
</aside>
