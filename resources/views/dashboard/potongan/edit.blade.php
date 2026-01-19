@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">Keuangan</li>
<li class="breadcrumb-item">
    <a href="{{ route('potongan.index') }}">Potongan Pegawai</a>
</li>
<li class="breadcrumb-item active text-primary fw-semibold">
    Edit Potongan
</li>
@endsection

@section('content')
<div class="card p-4">
<form action="{{ route('potongan.update', $potongan->id) }}" method="POST">
@csrf
@method('PUT')

<select name="pegawai_id" class="form-select mb-3">
@foreach($pegawais as $p)
<option value="{{ $p->id }}" {{ $p->id == $potongan->pegawai_id ? 'selected' : '' }}>
{{ $p->nama }} - {{ $p->nip }}
</option>
@endforeach
</select>

<input type="date" name="tanggal" class="form-control mb-3" value="{{ $potongan->tanggal }}">

@foreach($fields = [
 'potongan_wajib','potongan_pajak','potongan_bpjs','potongan_bpjs_lain',
 'dana_sosial','bank_jateng','bank_bjb','parcel','kop_sayuk_rukun','kop_mitra_lingua'
] as $f)
<input type="number" name="{{ $f }}" class="form-control hitung mb-2" value="{{ $potongan->$f }}">
@endforeach

<input type="text" id="total" class="form-control fw-bold text-danger mt-3" readonly>

<button class="btn btn-primary mt-3">Simpan</button>
</form>
</div>

<script>
function hitung() {
 let total = 0;
 document.querySelectorAll('.hitung').forEach(el => total += parseFloat(el.value)||0);
 document.getElementById('total').value = total.toLocaleString('id-ID');
}
document.querySelectorAll('.hitung').forEach(el => el.addEventListener('input', hitung));
hitung();
</script>
@endsection
