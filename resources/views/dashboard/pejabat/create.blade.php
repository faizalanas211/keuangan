@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('pejabat.index') }}">Data Pejabat</a>
</li>
<li class="breadcrumb-item active text-success fw-semibold">
    Tambah Pejabat
</li>
@endsection

@section('content')

<style>
.card-shadow {
    border: none;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
}
.btn-green{
    background:linear-gradient(135deg,#22c55e,#16a34a);
    border:none;
    color:#fff;
    font-weight:600;
}
</style>

<div class="row justify-content-center">
<div class="col-lg-8">

<div class="card card-shadow">
<div class="card-body">

<h5 class="text-success fw-semibold mb-4">Tambah Pejabat Periode</h5>

<form action="{{ route('pejabat.store') }}" method="POST">
@csrf

{{-- JENIS PEJABAT --}}
<div class="mb-3">
<label class="form-label">
    Jenis Pejabat <span class="text-danger">*</span>
</label>
<select name="jenis_pejabat_id" class="form-select" required>
    <option value="">-- Pilih Jenis --</option>
    @foreach($jenisPejabat as $j)
        <option value="{{ $j->id }}">
            {{ $j->nama }}
        </option>
    @endforeach
</select>
</div>

{{-- PEGAWAI --}}
<div class="mb-3">
<label class="form-label">
    Pegawai <span class="text-danger">*</span>
</label>
<select name="pegawai_id" id="pegawaiSelect" class="form-select" required>
    <option value="">-- Pilih Pegawai --</option>
    @foreach($pegawai as $p)
        <option value="{{ $p->id }}">
            {{ $p->nama }} - {{ $p->jabatan }}
        </option>
    @endforeach
</select>
</div>

{{-- PERIODE --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">
            Periode Mulai <span class="text-danger">*</span>
        </label>
        <input type="date" name="periode_mulai" class="form-control" required>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">
            Periode Selesai <span class="text-danger">*</span>
        </label>
        <input type="date" name="periode_selesai" class="form-control" required>
    </div>
</div>

{{-- STATUS --}}
<div class="mb-3">
<label class="form-label">Status</label>
<select name="is_active" class="form-select">
    <option value="1">Aktif</option>
    <option value="0">Nonaktif</option>
</select>
</div>

{{-- BUTTON --}}
<div class="text-end">
    <button type="submit" class="btn btn-green px-4">
        Simpan
    </button>
</div>

</form>

</div>
</div>

</div>
</div>

{{-- OPTIONAL: TOM SELECT --}}
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<script>
new TomSelect("#pegawaiSelect", {
    create: false,
    sortField: {
        field: "text",
        direction: "asc"
    }
});
</script>

@endsection