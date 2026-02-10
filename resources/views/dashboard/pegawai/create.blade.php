@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('pegawai.index') }}">Data Pegawai</a>
</li>
<li class="breadcrumb-item active text-success fw-semibold">
    Tambah Pegawai
</li>
@endsection

@section('content')

<style>
.btn-green{
    background:linear-gradient(135deg,#22c55e,#16a34a);
    border:none;
    color:#fff;
    font-weight:600;
}
.btn-green:hover{
    background:linear-gradient(135deg,#16a34a,#15803d);
}
</style>

<div class="row">
<div class="col-xxl">
<div class="card mb-4">

<div class="card-header d-flex align-items-center justify-content-between">
    <h5 class="mb-0 text-success fw-semibold">Tambah Data Pegawai</h5>
    <button class="btn btn-green" data-bs-toggle="modal" data-bs-target="#importModal">
        Import Excel
    </button>
</div>

<div class="card-body">

<div class="alert alert-success">
Setelah data pegawai disimpan, sistem otomatis membuat akun login.<br>
Login menggunakan <b>NIP</b> dengan kata sandi awal sesuai <b>NIP masing-masing pegawai</b>.
</div>

<form action="{{ route('pegawai.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="row mb-3">
<label class="col-sm-2 col-form-label">Nama</label>
<div class="col-sm-10">
<input type="text" name="nama" class="form-control" value="{{ old('nama') }}">
</div>
</div>

<div class="row mb-3">
<label class="col-sm-2 col-form-label">Status</label>
<div class="col-sm-10">
<select name="status" class="form-select">
<option value="aktif">Aktif</option>
<option value="nonaktif">Nonaktif</option>
</select>
</div>
</div>

<div class="row mb-3">
<label class="col-sm-2 col-form-label">NIP</label>
<div class="col-sm-10">
<input type="text" name="nip" class="form-control" value="{{ old('nip') }}">
</div>
</div>

<div class="row mb-3">
<label class="col-sm-2 col-form-label">Jenis Kelamin</label>
<div class="col-sm-10">
<select name="jenis_kelamin" class="form-select">
<option value="Laki-laki">Laki-laki</option>
<option value="Perempuan">Perempuan</option>
</select>
</div>
</div>

<div class="row mb-3">
<label class="col-sm-2 col-form-label">Jabatan</label>
<div class="col-sm-10">
<input type="text" name="jabatan" class="form-control" value="{{ old('jabatan') }}">
</div>
</div>

<div class="row mb-3">
<label class="col-sm-2 col-form-label">Pangkat, Golongan</label>
<div class="col-sm-10">
<input type="text" name="pangkat_golongan" class="form-control" value="{{ old('pangkat_golongan') }}">
</div>
</div>

<div class="row mb-3">
<label class="col-sm-2 col-form-label">Role</label>
<div class="col-sm-10">
<select name="role" class="form-select">
<option value="pegawai">Pegawai</option>
<option value="admin">Admin</option>
</select>
</div>
</div>

<div class="row mb-3">
<label class="col-sm-2 col-form-label">Foto</label>
<div class="col-sm-10">
<input type="file" name="foto" class="form-control">
</div>
</div>

<div class="row justify-content-end">
<div class="col-sm-10">
<button type="submit" class="btn btn-green">
Simpan
</button>
</div>
</div>

</form>
</div>
</div>
</div>
</div>

<!-- MODAL IMPORT -->
<div class="modal fade" id="importModal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Import Data Pegawai</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form action="{{ route('pegawai.import') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="modal-body">
<label class="form-label">Pilih File Excel</label>
<input type="file" name="file" class="form-control" required>

<div class="mt-2">
<a href="{{ asset('template/template_import_pegawai.xlsx') }}" class="text-success fw-semibold">
Download Template Excel
</a>
</div>
</div>

<div class="modal-footer">
<button type="submit" class="btn btn-green">Import</button>
</div>

</form>

</div>
</div>
</div>

@endsection
