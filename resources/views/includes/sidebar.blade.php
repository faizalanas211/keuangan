<aside id="layout-menu" class="layout-menu menu-vertical bg-menu-theme premium-sidebar">

<style>
.premium-sidebar{
    background:linear-gradient(180deg,#f0fdf4,#ffffff);
    border-radius:0 28px 28px 0;
    box-shadow:8px 0 28px rgba(16,185,129,.18);
    display:flex;
    flex-direction:column;
}

/* MENU */
.premium-sidebar .menu-inner{
    padding:0 14px;
    flex:1;
}

.premium-sidebar .menu-link{
    border-radius:14px;
    padding:11px 14px;
    font-weight:500;
}

.premium-sidebar .menu-item.active>.menu-link,
.premium-sidebar .menu-link:hover{
    background:linear-gradient(135deg,#22c55e,#16a34a);
    color:#fff!important;
}

/* PROFILE */
.sidebar-profile{
    margin:14px;
    padding:12px 14px;
    border-radius:16px;
    background:#ecfdf5;
    border:1px solid #86efac;
    display:flex;
    align-items:center;
    justify-content:space-between;
}

.profile-left{display:flex;align-items:center;gap:12px;}

.profile-img{
    width:42px;
    aspect-ratio:1/1;
    border-radius:50%;
    object-fit:cover;
    cursor:pointer;
}

.profile-img-lg{
    width:90px;
    aspect-ratio:1/1;
    border-radius:50%;
    object-fit:cover;
}

.logout-btn{
    width:34px;height:34px;border-radius:10px;
    background:#dcfce7;border:1px solid #86efac;
}
</style>

<div class="app-brand text-center py-3">
<span class="fw-bold text-success">Slip Gaji</span>
</div>

<ul class="menu-inner py-1">

<li class="menu-item {{ Request::is('dashboard')?'active':'' }}">
<a href="{{ route('dashboard') }}" class="menu-link">
<i class="menu-icon bx bx-home"></i><div>Dashboard</div>
</a>
</li>

@if(Auth::user()->role==='admin')

<li class="menu-header small text-uppercase">
<span class="menu-header-text">Keuangan</span>
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
<span class="menu-header-text">Master Data</span>
</li>

<li class="menu-item {{ Request::is('dashboard/pegawai*')?'active':'' }}">
<a href="{{ route('pegawai.index') }}" class="menu-link">
<i class="menu-icon bx bx-user"></i><div>Data Pegawai</div>
</a>
</li>



@endif

@if(Auth::user()->role==='pegawai')

<li class="menu-header small text-uppercase">
<span class="menu-header-text">Gaji Saya</span>
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
<div class="fw-bold">{{ Auth::user()->name }}</div>
<div class="text-success small">{{ ucfirst(Auth::user()->role) }}</div>
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

<!-- MODAL PROFILE -->
<div class="modal fade" id="profileModal">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Profil Akun</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body text-center">

<img src="{{ $pegawai && $pegawai->foto ? asset('storage/'.$pegawai->foto) : asset('admin/img/avatars/1.png') }}"
     class="profile-img-lg mb-3">

<h6>{{ Auth::user()->name }}</h6>
<p class="text-muted mb-3">{{ ucfirst(Auth::user()->role) }}</p>

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
<button class="btn btn-primary w-100 mb-3">Ganti Foto</button>
</form>

<form action="{{ route('logout') }}" method="POST">
@csrf
<button class="btn btn-outline-danger w-100">Logout</button>
</form>

</div>
</div>
</div>
</div>
