@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">Keuangan</li>
<li class="breadcrumb-item">
    <a href="{{ route('potongan.index') }}">Potongan Pegawai</a>
</li>
<li class="breadcrumb-item active text-primary fw-semibold">
    Tambah Potongan
</li>
@endsection

@section('content')
<div class="card p-4">
<form action="{{ route('potongan.store') }}" method="POST">
@csrf

{{-- Pegawai --}}
<div class="mb-3">
    <label class="form-label">Pegawai</label>
    <select name="pegawai_id" class="form-select">
        <option value="">-- Pilih Pegawai --</option>
        @foreach($pegawais as $p)
            <option value="{{ $p->id }}">{{ $p->nama }} - {{ $p->nip }}</option>
        @endforeach
    </select>
</div>

{{-- Periode --}}
<div class="mb-3">
    <label class="form-label">Periode</label>
    <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}">
</div>

@php
$fields = [
 'potongan_wajib' => 'Potongan Wajib',
 'potongan_pajak' => 'Potongan Pajak',
 'potongan_bpjs' => 'Potongan BPJS',
 'potongan_bpjs_lain' => 'Potongan BPJS Lain',
 'dana_sosial' => 'Dana Sosial',
 'bank_jateng' => 'Bank Jateng',
 'bank_bjb' => 'Bank BJB',
 'parcel' => 'Parcel',
 'kop_sayuk_rukun' => 'Kop. Sayuk Rukun',
 'kop_mitra_lingua' => 'Kop. Mitra Lingua',
];
@endphp

@foreach($fields as $name => $label)
<div class="mb-3">
    <label class="form-label">{{ $label }}</label>
    <input type="number" name="{{ $name }}" class="form-control hitung" value="0">
</div>
@endforeach

<div class="mb-4">
    <label class="form-label fw-bold">Total Potongan</label>
    <input type="text" id="total" class="form-control fw-bold text-danger" readonly>
</div>

<button class="btn btn-primary">Simpan</button>
<a href="{{ route('potongan.index') }}" class="btn btn-light">Batal</a>
</form>
</div>

<script>
function hitung() {
 let total = 0;
 document.querySelectorAll('.hitung').forEach(el => {
   total += parseFloat(el.value) || 0;
 });
 document.getElementById('total').value = total.toLocaleString('id-ID');
}
document.querySelectorAll('.hitung').forEach(el => el.addEventListener('input', hitung));
hitung();
</script>
@endsection
