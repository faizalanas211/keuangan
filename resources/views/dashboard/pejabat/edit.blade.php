@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('pegawai.index') }}">Data Pegawai</a>
</li>
<li class="breadcrumb-item active text-success fw-semibold">
    Edit Pegawai
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
    <h5 class="mb-0 text-success fw-semibold">Edit Data Pegawai</h5>
    <a href="{{ route('pegawai.index', ['page' => request('page')]) }}"
       class="btn btn-outline-success btn-sm">
        <i class="bx bx-arrow-back me-1"></i> Kembali
    </a>
</div>

<div class="card-body">
<form action="{{ route('pegawai.update', ['pegawai' => $pegawai->id, 'page' => request('page')]) }}"
      method="POST"
      enctype="multipart/form-data">
@csrf
@method('PUT')

<input type="hidden" name="page" value="{{ request('page') }}">

<div class="row mb-3">
<label class="col-sm-2 col-form-label">Nama Pegawai</label>
<div class="col-sm-10">
<input type="text" name="nama" class="form-control"
value="{{ old('nama', $pegawai->nama) }}">
</div>
</div>

<div class="row mb-3">
<label class="col-sm-2 col-form-label">Status</label>
<div class="col-sm-10">
<select name="status" class="form-select">
<option value="aktif" {{ old('status', $pegawai->status)=='aktif'?'selected':'' }}>Aktif</option>
<option value="nonaktif" {{ old('status', $pegawai->status)=='nonaktif'?'selected':'' }}>Nonaktif</option>
</select>
</div>
</div>

<div class="row mb-3">
<label class="col-sm-2 col-form-label">NIP</label>
<div class="col-sm-10">
<input type="text" name="nip" class="form-control"
value="{{ old('nip', $pegawai->nip) }}">
</div>
</div>

<div class="row mb-3">
<label class="col-sm-2 col-form-label">Jenis Kelamin</label>
<div class="col-sm-10">
<select name="jenis_kelamin" class="form-select">
<option value="Laki-laki" {{ old('jenis_kelamin',$pegawai->jenis_kelamin)=='Laki-laki'?'selected':'' }}>Laki-laki</option>
<option value="Perempuan" {{ old('jenis_kelamin',$pegawai->jenis_kelamin)=='Perempuan'?'selected':'' }}>Perempuan</option>
</select>
</div>
</div>

<div class="row mb-3">
<label class="col-sm-2 col-form-label">Jabatan</label>
<div class="col-sm-10">
<input type="text" name="jabatan" class="form-control"
value="{{ old('jabatan', $pegawai->jabatan) }}">
</div>
</div>

<div class="row mb-3">
<label class="col-sm-2 col-form-label">Pangkat, Golongan</label>
<div class="col-sm-10">
<input type="text" name="pangkat_golongan" class="form-control"
value="{{ old('pangkat_golongan', $pegawai->pangkat_golongan) }}">
</div>
</div>

<div class="row mb-3">
<label class="col-sm-2 col-form-label">Role</label>
<div class="col-sm-10">
<select name="role" class="form-select">
<option value="pegawai" {{ $pegawai->user->role=='pegawai'?'selected':'' }}>Pegawai</option>
<option value="admin" {{ $pegawai->user->role=='admin'?'selected':'' }}>Admin</option>
</select>
</div>
</div>

<div class="row mb-3">
<label class="col-sm-2 col-form-label">Foto</label>
<div class="col-sm-10">

@if ($pegawai->foto)
<div class="mb-2">
<img src="{{ asset('storage/'.$pegawai->foto) }}"
class="img-thumbnail"
style="max-width:150px">
</div>
@endif

<input type="file" name="foto" class="form-control">

<div class="form-text">JPG/JPEG/PNG • Maks 10 MB</div>

</div>
</div>

<div class="row justify-content-end">
<div class="col-sm-10">
<button type="submit" class="btn btn-green">
Simpan Perubahan
</button>
</div>
</div>

</form>
</div>
</div>
</div>
</div>
@endsection
