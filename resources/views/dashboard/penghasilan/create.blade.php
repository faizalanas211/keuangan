@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">Keuangan</li>
<li class="breadcrumb-item">
    <a href="{{ route('penghasilan.index') }}">Penghasilan Pegawai</a>
</li>
<li class="breadcrumb-item active text-success fw-semibold">
    Tambah Penghasilan
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
    <h5 class="mb-0 text-success fw-semibold">Tambah Penghasilan Pegawai</h5>

    <div>
        <button class="btn btn-green me-2"
                data-bs-toggle="modal"
                data-bs-target="#importModal">
            Import Excel
        </button>

        <a href="{{ route('penghasilan.index') }}"
           class="btn btn-outline-success btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="card-body">
<form action="{{ route('penghasilan.store') }}" method="POST">
@csrf

<div class="row mb-3">
<label class="col-sm-3 col-form-label">Pegawai</label>
<div class="col-sm-9">
<select name="pegawai_id" class="form-select">
<option value="">-- Pilih Pegawai --</option>
@foreach ($pegawais as $pegawai)
<option value="{{ $pegawai->id }}">
{{ $pegawai->nama }} - {{ $pegawai->nip }}
</option>
@endforeach
</select>
</div>
</div>

<div class="row mb-3">
<label class="col-sm-3 col-form-label">Periode (Bulan)</label>
<div class="col-sm-9">
<input type="date" name="tanggal" class="form-control"
value="{{ now()->toDateString() }}">
</div>
</div>

<div class="row mb-3">
<label class="col-sm-3 col-form-label">Gaji Induk</label>
<div class="col-sm-9">
<input type="number" name="gaji_induk"
class="form-control hitung" value="0">
</div>
</div>

@php
$fields = [
    'tunj_suami_istri' => 'Tunj. Suami / Istri',
    'tunj_anak' => 'Tunj. Anak',
    'tunj_umum' => 'Tunj. Umum',
    'tunj_struktural' => 'Tunj. Struktural',
    'tunj_fungsional' => 'Tunj. Fungsional',
    'tunj_beras' => 'Tunj. Beras',
    'tunj_pajak' => 'Tunj. Pajak',
    'pembulatan' => 'Pembulatan',
];
@endphp

@foreach ($fields as $name => $label)
<div class="row mb-3">
<label class="col-sm-3 col-form-label">{{ $label }}</label>
<div class="col-sm-9">
<input type="number" name="{{ $name }}"
class="form-control hitung" value="0">
</div>
</div>
@endforeach

<div class="row mb-4">
<label class="col-sm-3 col-form-label fw-bold">Total Penghasilan</label>
<div class="col-sm-9">
<input type="text" id="total_penghasilan"
class="form-control fw-bold text-success" readonly>
</div>
</div>

<div class="row justify-content-end">
<div class="col-sm-9">
<button type="submit" class="btn btn-green">
Simpan
</button>
<a href="{{ route('penghasilan.index') }}"
class="btn btn-light ms-2">Batal</a>
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
<h5 class="modal-title">Import Penghasilan Pegawai</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form action="{{ route('penghasilan.import') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="modal-body">
<label class="form-label">Pilih File Excel</label>
<input type="file" name="file" class="form-control" required>

<div class="mt-2">
<a href="{{ asset('template/template_import_penghasilan.xlsx') }}"
class="text-success fw-semibold">
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

<script>
function hitungTotal(){
let total=0;
document.querySelectorAll('.hitung').forEach(el=>{
total+=parseFloat(el.value)||0;
});
document.getElementById('total_penghasilan').value=total.toLocaleString('id-ID');
}
document.querySelectorAll('.hitung').forEach(el=>el.addEventListener('input',hitungTotal));
hitungTotal();
</script>
@endsection
