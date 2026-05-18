<aside id="layout-menu" class="layout-menu menu-vertical bg-menu-theme {{ Auth::user()->role === 'pegawai' ? 'sidebar-pegawai' : 'premium-sidebar' }}">

<style>
/* ===== SIDEBAR ADMIN (HIJAU TUA) - TETAP SEPERTI ASLI ===== */
.premium-sidebar{
    background: linear-gradient(145deg, #064e3b 0%, #065f46 100%);
    border-radius: 0 32px 32px 0;
    box-shadow: 12px 0 32px rgba(0,0,0,.25);
    display:flex;
    flex-direction:column;
    backdrop-filter: blur(2px);
}

/* MENU STYLE - ADMIN */
.premium-sidebar .menu-inner{
    padding: 0 16px;
    flex:1;
}

.premium-sidebar .menu-link{
    border-radius: 12px;
    padding: 12px 16px;
    font-weight: 500;
    transition: all 0.3s ease;
    color: #d1fae5 !important;
    backdrop-filter: blur(4px);
}

.premium-sidebar .menu-item.active>.menu-link,
.premium-sidebar .menu-link:hover{
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(16,185,129,0.4);
    transform: translateX(4px);
    letter-spacing: 0.3px;
}

.premium-sidebar .menu-item.active>.menu-link i,
.premium-sidebar .menu-link:hover i {
    text-shadow: 0 0 8px rgba(255,255,255,0.5);
}

.premium-sidebar .menu-header {
    padding: 12px 16px 6px 16px;
    color: #6ee7b7 !important;
    letter-spacing: 1px;
    font-weight: 600;
    border-bottom: 1px dashed rgba(110,231,183,0.3);
    margin-bottom: 8px;
    margin-top: 8px;
}

.premium-sidebar .menu-header-text {
    display: inline-block;
    padding-bottom: 4px;
}

.premium-sidebar .menu-header {
    border-bottom: none;
    position: relative;
    padding-left: 0;
}

.premium-sidebar .menu-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 16px;
    right: 16px;
    height: 1px;
    background: linear-gradient(90deg, rgba(110,231,183,0.3), rgba(110,231,183,0.1), transparent);
}

/* PROFILE SECTION - ADMIN */
.premium-sidebar .sidebar-profile{
    margin: 16px;
    padding: 14px 16px;
    border-radius: 24px;
    background: rgba(6, 78, 59, 0.7);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(52, 211, 153, 0.4);
    display:flex;
    align-items:center;
    justify-content:space-between;
    transition: all 0.3s ease;
}

.premium-sidebar .sidebar-profile:hover {
    border-color: #34d399;
    background: rgba(6, 78, 59, 0.85);
}

/* ===== SIDEBAR PEGAWAI (KUNING MADU) ===== */
.sidebar-pegawai{
    background: linear-gradient(145deg, #d97706 0%, #b45309 100%);
    border-radius: 0 32px 32px 0;
    box-shadow: 12px 0 32px rgba(0,0,0,.25);
    display:flex;
    flex-direction:column;
    backdrop-filter: blur(2px);
}

.sidebar-pegawai .menu-inner{
    padding: 0 16px;
    flex:1;
}

/* Menu normal - teks PUTIH */
.sidebar-pegawai .menu-link{
    border-radius: 12px;
    padding: 12px 16px;
    font-weight: 500;
    transition: all 0.3s ease;
    color: #ffffff !important;
    backdrop-filter: blur(4px);
}

/* Hover menu - teks PUTIH */
.sidebar-pegawai .menu-link:hover{
    background: linear-gradient(135deg, #fde047, #eab308) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(253,224,71,0.4);
    transform: translateX(4px);
    letter-spacing: 0.3px;
}

.sidebar-pegawai .menu-link:hover .menu-icon {
    color: #ffffff !important;
}

/* Menu AKTIF - background kuning, teks PUTIH */
.sidebar-pegawai .menu-item.active>.menu-link {
    background: linear-gradient(135deg, #fef08a, #fde047) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(253,224,71,0.4);
    transform: translateX(4px);
    letter-spacing: 0.3px;
    font-weight: 600;
}

.sidebar-pegawai .menu-item.active>.menu-link .menu-icon {
    color: #ffffff !important;
}

.sidebar-pegawai .menu-item.active>.menu-link div {
    color: #ffffff !important;
}

/* Menu header (judul grup) - warna kuning muda agar kontras */
.sidebar-pegawai .menu-header {
    padding: 12px 16px 6px 16px;
    color: #fef3c7 !important;
    letter-spacing: 1px;
    font-weight: 600;
    border-bottom: 1px dashed rgba(253,230,138,0.5);
    margin-bottom: 8px;
    margin-top: 8px;
}

.sidebar-pegawai .menu-header-text {
    display: inline-block;
    padding-bottom: 4px;
}

.sidebar-pegawai .menu-header {
    border-bottom: none;
    position: relative;
    padding-left: 0;
}

.sidebar-pegawai .menu-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 16px;
    right: 16px;
    height: 1px;
    background: linear-gradient(90deg, rgba(253,230,138,0.5), rgba(253,230,138,0.2), transparent);
}

/* Ikon menu - PUTIH */
.sidebar-pegawai .menu-icon {
    color: #ffffff !important;
}

/* PROFILE SECTION - PEGAWAI */
.sidebar-pegawai .sidebar-profile{
    margin: 16px;
    padding: 14px 16px;
    border-radius: 24px;
    background: rgba(120, 53, 15, 0.7);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(253, 224, 71, 0.5);
    display:flex;
    align-items:center;
    justify-content:space-between;
    transition: all 0.3s ease;
}

.sidebar-pegawai .sidebar-profile:hover {
    border-color: #fde047;
    background: rgba(120, 53, 15, 0.85);
}

/* ===== STYLE BERSAMA UNTUK KEDUA SIDEBAR ===== */
.profile-left{display:flex;align-items:center;gap:12px;}

.profile-img{
    width: 44px;
    aspect-ratio:1/1;
    border-radius: 50%;
    object-fit:cover;
    cursor:pointer;
    border: 2px solid #34d399;
    box-shadow: 0 0 12px rgba(52,211,153,0.3);
    transition: transform 0.2s ease;
}

.profile-img:hover {
    transform: scale(1.05);
}

.profile-img-lg{
    width: 100px;
    aspect-ratio:1/1;
    border-radius: 50%;
    object-fit:cover;
    border: 3px solid #10b981;
    box-shadow: 0 0 20px rgba(16,185,129,0.4);
}

.logout-btn{
    width: 36px;
    height: 36px;
    border-radius: 12px;
    background: rgba(52, 211, 153, 0.2);
    border: 1px solid rgba(52, 211, 153, 0.5);
    color: #6ee7b7;
    transition: all 0.2s ease;
}

.logout-btn:hover {
    background: #059669;
    color: white;
    border-color: #059669;
    transform: scale(1.05);
}

/* Style khusus untuk sidebar pegawai agar tombol logout match */
.sidebar-pegawai .logout-btn {
    background: rgba(253, 224, 71, 0.2);
    border: 1px solid rgba(253, 224, 71, 0.5);
    color: #fef3c7;
}

.sidebar-pegawai .logout-btn:hover {
    background: #fde047;
    color: #78350f;
    border-color: #fde047;
}

/* PERBAIKAN: App Brand untuk pegawai */
.sidebar-pegawai .app-brand {
    border-bottom: 1px solid rgba(253, 224, 71, 0.4);
}

.sidebar-pegawai .app-brand .bx-wallet {
    color: #fef08a !important;
    filter: drop-shadow(0 0 6px #fde047) !important;
}

.sidebar-pegawai .app-brand .fw-bold {
    background: linear-gradient(135deg, #fef3c7, #fef08a) !important;
    -webkit-background-clip: text !important;
    background-clip: text !important;
    color: transparent !important;
}

/* App Brand Style - Admin (tetap) */
.app-brand {
    border-bottom: 1px solid rgba(52, 211, 153, 0.3);
    margin-bottom: 8px;
}

.app-brand .text-success {
    background: linear-gradient(135deg, #34d399, #10b981);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent !important;
    text-shadow: none;
}

/* Icons Style */
.menu-icon {
    font-size: 1.3rem;
    margin-right: 10px;
}

/* Scrollbar Styling - Admin */
.premium-sidebar::-webkit-scrollbar {
    width: 4px;
}

.premium-sidebar::-webkit-scrollbar-track {
    background: rgba(110,231,183,0.1);
    border-radius: 4px;
}

.premium-sidebar::-webkit-scrollbar-thumb {
    background: #34d399;
    border-radius: 4px;
}

/* Scrollbar Styling - Pegawai */
.sidebar-pegawai::-webkit-scrollbar {
    width: 4px;
}

.sidebar-pegawai::-webkit-scrollbar-track {
    background: rgba(253,230,138,0.1);
    border-radius: 4px;
}

.sidebar-pegawai::-webkit-scrollbar-thumb {
    background: #fbbf24;
    border-radius: 4px;
}

/* ===== MODAL PROFILE - UNTUK PEGAWAI ===== */
.role-pegawai .modal-content {
    background: linear-gradient(145deg, #d97706, #b45309);
    border: 1px solid #fbbf24;
    color: #fef3c7;
}

.role-pegawai .modal-header {
    border-bottom-color: rgba(253, 224, 71, 0.3);
}

.role-pegawai .modal-header .modal-title {
    color: #fef08a !important;
}

.role-pegawai #profileModal .form-control {
    background: rgba(120, 53, 15, 0.3);
    border: 1px solid rgba(253, 224, 71, 0.4);
    color: #fef3c7;
}

.role-pegawai #profileModal .form-control:focus {
    background: rgba(120, 53, 15, 0.5);
    border-color: #fde047;
    box-shadow: 0 0 8px rgba(253, 224, 71, 0.3);
    color: #ffffff;
}

.role-pegawai #profileModal .btn-success {
    background: linear-gradient(135deg, #f59e0b, #d97706) !important;
    border: none !important;
    color: white !important;
}

.role-pegawai #profileModal .btn-success:hover {
    background: linear-gradient(135deg, #d97706, #b45309) !important;
}

/* Modal untuk admin - tetap hijau */
.modal-content {
    background: linear-gradient(145deg, #064e3b, #065f46);
    border: 1px solid #34d399;
    color: #d1fae5;
}

.modal-header {
    border-bottom-color: rgba(52,211,153,0.3);
}

.btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
}

.btn-success:hover {
    background: linear-gradient(135deg, #059669, #047857);
    transform: translateY(-1px);
}

#profileModal .form-control {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(52,211,153,0.3);
    color: #d1fae5;
}

#profileModal .form-control:focus {
    background: rgba(255,255,255,0.15);
    border-color: #34d399;
    box-shadow: 0 0 8px rgba(52,211,153,0.3);
    color: #ffffff;
}

#profileModal .form-control::placeholder {
    color: rgba(209,250,229,0.5);
}

/* ===== CSS PALING KUAT UNTUK MEMAKSA WARNA PUTIH DI SIDEBAR PEGAWAI ===== */
.sidebar-pegawai .menu-link,
.sidebar-pegawai .menu-link *,
.sidebar-pegawai .menu-item .menu-link,
.sidebar-pegawai .menu-item .menu-link div,
.sidebar-pegawai .menu-item .menu-link i,
.sidebar-pegawai .menu-item.active .menu-link,
.sidebar-pegawai .menu-item.active .menu-link div,
.sidebar-pegawai .menu-item.active .menu-link i {
    color: #ffffff !important;
    background-color: transparent !important;
}

.sidebar-pegawai .menu-item.active .menu-link {
    background: linear-gradient(135deg, #fef08a, #fde047) !important;
    color: #ffffff !important;
}

.sidebar-pegawai .menu-link:hover,
.sidebar-pegawai .menu-link:hover div,
.sidebar-pegawai .menu-link:hover i {
    color: #ffffff !important;
}
</style>

<div class="app-brand py-4 px-6 text-center">
    <div class="d-flex justify-content-center align-items-center gap-2">
        <div class="bx bx-wallet" style="font-size: 36px; {{ Auth::user()->role === 'pegawai' ? 'color: #fef08a; filter: drop-shadow(0 0 6px #fde047);' : 'color: #34d399; filter: drop-shadow(0 0 6px #10b981);' }}"></div>
        <div class="text-start">
            <div class="fw-bold" style="font-size: 22px; line-height:1.2; {{ Auth::user()->role === 'pegawai' ? 'background: linear-gradient(135deg, #fef3c7, #fef08a); -webkit-background-clip: text; background-clip: text; color: transparent;' : 'background: linear-gradient(135deg, #6ee7b7, #34d399); -webkit-background-clip: text; background-clip: text; color: transparent;' }}">
                Slip Gaji
            </div>
        </div>
    </div>
</div>

<ul class="menu-inner py-1">

<li class="menu-item {{ Request::is('dashboard')?'active':'' }}">
    <a href="{{ route('dashboard') }}" class="menu-link">
        <i class="menu-icon bx bx-home"></i><div>Dashboard</div>
    </a>
</li>

@if(Auth::user()->role==='admin')

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Master Data</span>
</li>

<li class="menu-item {{ Request::is('dashboard/pegawai*') ? 'active' : '' }}">
    <a href="{{ route('pegawai.index') }}" class="menu-link">
        <i class="menu-icon bx bx-group"></i>
        <div>Data Pegawai</div>
    </a>
</li>
<li class="menu-item {{ Request::is('dashboard/pejabat*') ? 'active' : '' }}">
    <a href="{{ route('pejabat.index') }}" class="menu-link">
        <i class="menu-icon bx bx-briefcase-alt-2"></i>
        <div>Data Pejabat</div>
    </a>
</li>

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Gaji</span>
</li>

<li class="menu-item {{ Request::is('dashboard/penghasilan*')?'active':'' }}">
    <a href="{{ route('penghasilan.index') }}" class="menu-link">
        <i class="menu-icon bx bx-plus-circle"></i><div>Penghasilan</div>
    </a>
</li>

<li class="menu-item {{ Request::is('dashboard/potongan*')?'active':'' }}">
    <a href="{{ route('potongan.index') }}" class="menu-link">
        <i class="menu-icon bx bx-minus-circle"></i><div>Potongan</div>
    </a>
</li>

<li class="menu-item {{ Request::is('dashboard/slip-gaji*')?'active':'' }}">
    <a href="{{ route('slip-gaji.index') }}" class="menu-link">
        <i class="menu-icon bx bx-printer"></i><div>Slip Gaji</div>
    </a>
</li>

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Biaya Perjadin</span>
</li>

<li class="menu-item {{ Request::is('dashboard/perjadin*')?'active':'' }}">
    <a href="{{ route('perjadin.index') }}" class="menu-link">
        <i class="menu-icon bx bx-car"></i><div>Perjadin</div>
    </a>
</li>

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Pengaturan</span>
</li>

<li class="menu-item {{ Request::is('dashboard/pengaturan*') ? 'active' : '' }}">
    <a href="{{ route('template.index') }}" class="menu-link">
        <i class="menu-icon bx bx-file"></i>
        <div>Template Dokumen</div>
    </a>
</li>

@endif

@if(Auth::user()->role==='pegawai')

<li class="menu-item {{ Request::is('dashboard/slip-gaji*')?'active':'' }}">
    <a href="{{ route('slip-gaji.index') }}" class="menu-link">
        <i class="menu-icon bx bx-receipt"></i><div>Slip Gaji</div>
    </a>
</li>

<li class="menu-item {{ Request::is('dashboard/perjadin*')?'active':'' }}">
    <a href="{{ route('perjadin.index') }}" class="menu-link">
        <i class="menu-icon bx bx-receipt"></i><div>Perjalanan Dinas</div>
    </a>
</li>

@endif

</ul>

@php
$pegawai = auth()->user()->pegawai;
@endphp

<div class="sidebar-profile">
    <div class="profile-left">
        <img src="{{ $pegawai && $pegawai->foto ? asset('storage/'.$pegawai->foto) : asset('admin/img/avatars/1.png') }}"
             class="profile-img">
        <div>
            <div class="fw-bold" style="{{ Auth::user()->role === 'pegawai' ? 'color: #ffffff;' : 'color: #d1fae5;' }}">{{ Auth::user()->name }}</div>
            <div class="small" style="{{ Auth::user()->role === 'pegawai' ? 'color: #fde047;' : 'color: #34d399;' }}">{{ ucfirst(Auth::user()->role) }}</div>
        </div>
    </div>
    <button class="logout-btn" data-bs-toggle="modal" data-bs-target="#profileModal" title="Profil">
        <i class="bx bx-user-circle"></i>
    </button>
</div>

</aside>

<!-- MODAL PROFILE -->
<div class="modal fade" id="profileModal">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" style="{{ Auth::user()->role === 'pegawai' ? 'color: #fef08a;' : 'color: #6ee7b7;' }}">Profil Akun</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body text-center">
        <img src="{{ $pegawai && $pegawai->foto ? asset('storage/'.$pegawai->foto) : asset('admin/img/avatars/1.png') }}"
             class="profile-img-lg mb-3">
        <h6 style="{{ Auth::user()->role === 'pegawai' ? 'color: #ffffff;' : 'color: #d1fae5;' }}">{{ Auth::user()->name }}</h6>
        <p class="mb-3" style="{{ Auth::user()->role === 'pegawai' ? 'color: #fde047;' : 'color: #6ee7b7;' }}">{{ ucfirst(Auth::user()->role) }}</p>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="password" name="password_lama" class="form-control mb-2" placeholder="Password Lama">
            <input type="password" name="password_baru" class="form-control mb-2" placeholder="Password Baru">
            <input type="password" name="password_baru_confirmation" class="form-control mb-3" placeholder="Konfirmasi Password">
            <button class="btn btn-success w-100 mb-3">Ganti Password</button>
        </form>

        <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="foto" class="form-control mb-2">
            <button class="btn btn-success w-100 mb-3">Ganti Foto</button>
        </form>

        <form action="{{ route('logout') }}" method="POST" id="logoutForm">
            @csrf
            <button type="submit" class="btn w-100" style="background: transparent; border: 1px solid #ef4444; color: #fca5a5;">Logout</button>
        </form>
    </div>
</div>
</div>
</div>

<script>
document.getElementById('logoutForm')?.addEventListener('submit', function(e) {});
</script>