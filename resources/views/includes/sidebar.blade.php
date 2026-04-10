<aside id="layout-menu" class="layout-menu menu-vertical bg-menu-theme premium-sidebar">

<style>
.premium-sidebar{
    background: linear-gradient(145deg, #064e3b 0%, #065f46 100%);
    border-radius: 0 32px 32px 0;
    box-shadow: 12px 0 32px rgba(0,0,0,.25);
    display:flex;
    flex-direction:column;
    backdrop-filter: blur(2px);
}

/* MENU STYLE BARU - Glassmorphism + Neon */
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

/* Menu Header Style Baru */
/* Perbaiki spacing menu header */
.premium-sidebar .menu-header {
    padding: 12px 16px 6px 16px;
    color: #6ee7b7 !important;
    letter-spacing: 1px;
    font-weight: 600;
    border-bottom: 1px dashed rgba(110,231,183,0.3);
    margin-bottom: 8px;
    margin-top: 8px;
}

/* Beri jarak antara border dan teks */
.premium-sidebar .menu-header-text {
    display: inline-block;
    padding-bottom: 4px;
}

/* Atau alternatif: border-bottom dihilangkan */
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

/* PROFILE SECTION - Style Baru dengan Card Effect */
.sidebar-profile{
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

.sidebar-profile:hover {
    border-color: #34d399;
    background: rgba(6, 78, 59, 0.85);
}

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

/* App Brand Style Baru */
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

/* Scrollbar Styling */
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

/* Modal Style Update */
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

/* ========== PERBAIKAN: CSS KHUSUS UNTUK MODAL PROFILE ========== */
/* Style ini hanya berlaku untuk form di dalam modal profile */
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

/* Style untuk input di luar modal (halaman utama) akan tetap normal */
/* Tidak perlu menambahkan CSS global .form-control lagi */
</style>

<div class="app-brand py-4 px-6 text-center">
    <div class="d-flex justify-content-center align-items-center gap-2">
        <div class="bx bx-wallet" style="font-size: 36px; color: #34d399; filter: drop-shadow(0 0 6px #10b981);"></div>
        <div class="text-start">
            <div class="fw-bold" style="font-size: 22px; line-height:1.2; background: linear-gradient(135deg, #6ee7b7, #34d399); -webkit-background-clip: text; background-clip: text; color: transparent;">
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

<li class="menu-header small text-uppercase">
<span class="menu-header-text">📄 Gaji Saya</span>
</li>

<li class="menu-item {{ Request::is('dashboard/slip-gaji*')?'active':'' }}">
<a href="{{ route('slip-gaji.index') }}" class="menu-link">
<i class="menu-icon bx bx-receipt"></i><div>Slip Gaji</div>
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
<div class="fw-bold" style="color: #d1fae5;">{{ Auth::user()->name }}</div>
<div class="small" style="color: #34d399;">{{ ucfirst(Auth::user()->role) }}</div>
</div>
</div>

<button class="logout-btn"
        data-bs-toggle="modal"
        data-bs-target="#profileModal"
        title="Profil">
    <i class="bx bx-user-circle"></i>
</button>

</div>

</aside>

<!-- MODAL PROFILE dengan tema gelap hijau -->
<div class="modal fade" id="profileModal">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title" style="color: #6ee7b7;">Profil Akun</h5>
<button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body text-center">

<img src="{{ $pegawai && $pegawai->foto ? asset('storage/'.$pegawai->foto) : asset('admin/img/avatars/1.png') }}"
     class="profile-img-lg mb-3">

<h6 style="color: #d1fae5;">{{ Auth::user()->name }}</h6>
<p class="mb-3" style="color: #6ee7b7;">{{ ucfirst(Auth::user()->role) }}</p>

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
<button class="btn btn-success w-100 mb-3" style="background: #059669;">Ganti Foto</button>
</form>

<form action="{{ route('logout') }}" method="POST">
@csrf
<button class="btn w-100" style="background: transparent; border: 1px solid #ef4444; color: #fca5a5;">Logout</button>
</form>

</div>
</div>
</div>
</div>