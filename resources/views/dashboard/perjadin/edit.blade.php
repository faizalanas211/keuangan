@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('perjadin.index') }}">Data Perjalanan Dinas</a>
</li>
<li class="breadcrumb-item active text-success fw-semibold">
    Edit Perjalanan Dinas
</li>
@endsection

@section('content')

<style>
.card-shadow {
    border: none;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
}
.section-title {
    font-weight: 600;
    color: #16a34a;
}
</style>

<form action="{{ route('perjadin.update',$perjalanan->id) }}" method="POST">
@csrf
@method('PUT')

{{-- ================= INFORMASI ================= --}}
<div class="card card-shadow mb-4">
<div class="card-body">

<h5 class="section-title mb-3">Informasi Perjalanan</h5>

<div class="row mb-3">
    <div class="col-md-6">
        <label>Tingkat Perjalanan</label>
        <input type="text" name="tingkat_perjalanan" class="form-control"
        value="{{ old('tingkat_perjalanan',$perjalanan->tingkat_perjalanan) }}">
    </div>
    <div class="col-md-6">
        <label>Alat Angkutan</label>
        <input type="text" name="alat_angkutan" class="form-control"
        value="{{ old('alat_angkutan',$perjalanan->alat_angkutan) }}">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label>Dari Kota</label>
        <input type="text" name="dari_kota" class="form-control"
        value="{{ old('dari_kota',$perjalanan->dari_kota) }}">
    </div>
    <div class="col-md-6">
        <label>Tujuan Kota</label>
        <input type="text" name="tujuan_kota" class="form-control"
        value="{{ old('tujuan_kota',$perjalanan->tujuan_kota) }}">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label>Tanggal Mulai</label>
        <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control"
        value="{{ old('tanggal_mulai', \Carbon\Carbon::parse($perjalanan->tanggal_mulai)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-6">
        <label>Tanggal Akhir</label>
        <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control"
        value="{{ old('tanggal_akhir', \Carbon\Carbon::parse($perjalanan->tanggal_akhir)->format('Y-m-d')) }}">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label>Tanggal Terima</label>
        <input type="date" id="tanggal_terima" name="tanggal_terima" class="form-control"
        value="{{ old('tanggal_terima',
                $perjalanan->tanggal_terima 
                    ? \Carbon\Carbon::parse($perjalanan->tanggal_terima)->format('Y-m-d') 
                    : ''
            ) }}">
    </div>
    <div class="col-md-6">
        <label>Kode MAK</label>
        <input type="text" name="kode_mak" class="form-control"
        value="{{ old('kode_mak',$perjalanan->kode_mak) }}">
    </div>
</div>

<div class="mb-3">
    <label>Akun Biaya</label>
    <textarea name="akun_biaya" class="form-control">{{ old('akun_biaya',$perjalanan->akun_biaya) }}</textarea>
</div>

<div class="mb-3">
    <label>Nama Kegiatan</label>
    <textarea name="nama_kegiatan" class="form-control">{{ old('nama_kegiatan',$perjalanan->nama_kegiatan) }}</textarea>
</div>

</div>
</div>

{{-- ================= SURAT PERJALANAN ================= --}}
<div class="card card-shadow mb-4">
    <div class="card-body">

        <h5 class="section-title mb-3">Surat Perjalanan</h5>

        <div class="row mb-3">
    <div class="col-md-6">
        <label>Tanggal ST <span class="text-danger">*</span></label>
        <input type="date" 
            name="tanggal_st" 
            class="form-control"
            value="{{ old('tanggal_st',
                $perjalanan->surat && $perjalanan->surat->tanggal_st
                    ? \Carbon\Carbon::parse($perjalanan->surat->tanggal_st)->format('Y-m-d')
                    : ''
            ) }}">
    </div>

    <div class="col-md-6">
        <label>Nomor ST <span class="text-danger">*</span></label>
        <input type="text" 
               name="nomor_st" 
               class="form-control"
               value="{{ old('nomor_st', $perjalanan->surat->nomor_st ?? '') }}">
    </div>
</div>

<div class="mb-3">
    <label>Nomor SK</label>
    <input type="text" 
            name="nomor_sk" 
            class="form-control"
            value="{{ old('nomor_sk', $perjalanan->surat->nomor_sk ?? '') }}">
</div>

    </div>
</div>

{{-- ================= PEGAWAI ================= --}}
<div class="card card-shadow mb-4">
<div class="card-body">

<h5 class="section-title mb-3">Pilih Pegawai</h5>

<select name="pegawai[]" id="pegawaiSelect" class="form-select" multiple>
@foreach($pegawai as $p)
<option value="{{ $p->id }}"
{{ $perjalanan->pegawaiPerjalanan->pluck('pegawai_id')->contains($p->id) ? 'selected' : '' }}>
{{ $p->nama }} - {{ $p->jabatan }}
</option>
@endforeach
</select>

</div>
</div>

{{-- ================= RINCIAN ================= --}}
<div class="card card-shadow mb-4">
<div class="card-body">

<div class="d-flex justify-content-between mb-3">
    <h5 class="section-title mb-0">Rincian Biaya</h5>
    <button type="button" class="btn btn-sm btn-outline-success" onclick="copyToAll()">
        Salin ke Semua
    </button>
</div>

<div id="rincianContainer"></div>

</div>
</div>

<div class="text-end">
<button class="btn btn-success">Simpan</button>
</div>

</form>

{{-- ================= SCRIPT ================= --}}

<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<script>
const jenisBiayaData = @json($jenisBiaya);
const existingRincian = @json(
    $perjalanan->pegawaiPerjalanan->mapWithKeys(function($pp){
        return [
            $pp->pegawai_id => $pp->rincian
        ];
    })
);
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const select = new TomSelect("#pegawaiSelect", {
        plugins: ['remove_button'],
        onInitialize() {
            generateRincian(this.getValue());
        },
        onChange(values) {
            generateRincian(values);
        }
    });

});

function generateRincian(selectedIds) {

    const container = document.getElementById('rincianContainer');
    container.innerHTML = '';

    selectedIds.forEach(pegawaiId => {

        const nama = document.querySelector(`#pegawaiSelect option[value="${pegawaiId}"]`).text;

        let rows = '';

        (existingRincian[pegawaiId] || []).forEach(r => {

            const idx = Date.now() + Math.floor(Math.random()*1000);

            rows += `
            <tr>
                <td>
                    <select name="rincian[${pegawaiId}][${idx}][jenis_biaya_id]" class="form-select">
                        ${jenisBiayaData.map(j => `
                            <option value="${j.id}" ${j.id == r.jenis_biaya_id ? 'selected' : ''}>
                                ${j.nama_biaya}
                            </option>
                        `).join('')}
                    </select>
                </td>
                <td>
                    <input type="text"
                        name="rincian[${pegawaiId}][${idx}][uraian]"
                        value="${r.uraian ?? ''}"
                        class="form-control">
                </td>
                <td>
                    <input type="number"
                        name="rincian[${pegawaiId}][${idx}][volume]"
                        value="${r.volume}"
                        class="form-control volume-field">
                </td>
                <td>
                    <input type="text"
                        name="rincian[${pegawaiId}][${idx}][satuan]"
                        value="${r.satuan}"
                        class="form-control">
                </td>
                <td>
                    <input type="number"
                        name="rincian[${pegawaiId}][${idx}][tarif]"
                        value="${r.tarif}"
                        class="form-control tarif-field">
                </td>
                <td>
                    <input type="number"
                        name="rincian[${pegawaiId}][${idx}][total]"
                        value="${r.total}"
                        class="form-control total-field"
                        readonly>
                </td>
                <td>
                    <button type="button"
                        class="btn btn-danger btn-sm"
                        onclick="this.closest('tr').remove()">Hapus</button>
                </td>
            </tr>
            `;
        });

        container.innerHTML += `
        <div class="border rounded p-3 mb-4">
            <h6 class="fw-bold text-success">${nama}</h6>

            <button type="button" class="btn btn-sm btn-outline-success mb-2"
                onclick="addRincianRow(${pegawaiId})">
                + Tambah Rincian
            </button>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Uraian</th>
                        <th>Volume</th>
                        <th>Satuan</th>
                        <th>Tarif</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody-${pegawaiId}">
                    ${rows}
                </tbody>
            </table>
        </div>
        `;
    });
}

function addRincianRow(pegawaiId) {

    const tbody = document.getElementById(`tbody-${pegawaiId}`);
    const index = Date.now();

    let jenisOptions = jenisBiayaData.map(j =>
        `<option value="${j.id}">${j.nama_biaya}</option>`
    ).join('');

    tbody.insertAdjacentHTML('beforeend', `
    <tr>
        <td>
            <select name="rincian[${pegawaiId}][${index}][jenis_biaya_id]" class="form-select">
                ${jenisOptions}
            </select>
        </td>
        <td>
            <input type="text" name="rincian[${pegawaiId}][${index}][uraian]" class="form-control">
        </td>
        <td>
            <input type="number" name="rincian[${pegawaiId}][${index}][volume]" class="form-control volume-field">
        </td>
        <td>
            <input type="text" name="rincian[${pegawaiId}][${index}][satuan]" class="form-control">
        </td>
        <td>
            <input type="number" name="rincian[${pegawaiId}][${index}][tarif]" class="form-control tarif-field">
        </td>
        <td>
            <input type="number" name="rincian[${pegawaiId}][${index}][total]" class="form-control total-field" readonly>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">Hapus</button>
        </td>
    </tr>
    `);
}

document.addEventListener('input', function(e){
    if (e.target.classList.contains('volume-field') || e.target.classList.contains('tarif-field')) {
        const row = e.target.closest('tr');
        const v = row.querySelector('.volume-field').value || 0;
        const t = row.querySelector('.tarif-field').value || 0;
        row.querySelector('.total-field').value = v * t;
    }
});

function copyToAll() {

    const tables = document.querySelectorAll('#rincianContainer tbody');

    if (tables.length <= 1) {
        alert('Minimal pilih 2 pegawai');
        return;
    }

    const firstRows = tables[0].querySelectorAll('tr');

    for (let i = 1; i < tables.length; i++) {

        const targetTbody = tables[i];
        const pegawaiId = targetTbody.id.replace('tbody-', '');

        targetTbody.innerHTML = '';

        firstRows.forEach(row => {

            const newIndex = Date.now() + Math.floor(Math.random()*1000);

            const jenis = row.querySelector('select').value;
            const uraian = row.querySelector('input[name*="[uraian]"]').value;
            const volume = row.querySelector('.volume-field').value;
            const satuan = row.querySelector('input[name*="[satuan]"]').value;
            const tarif = row.querySelector('.tarif-field').value;
            const total = row.querySelector('.total-field').value;

            targetTbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td>
                    <select name="rincian[${pegawaiId}][${newIndex}][jenis_biaya_id]" class="form-select">
                        ${jenisBiayaData.map(j => `
                            <option value="${j.id}" ${j.id == jenis ? 'selected' : ''}>
                                ${j.nama_biaya}
                            </option>
                        `).join('')}
                    </select>
                </td>
                <td>
                    <input type="text"
                        name="rincian[${pegawaiId}][${newIndex}][uraian]"
                        value="${uraian}"
                        class="form-control">
                </td>
                <td>
                    <input type="number"
                        name="rincian[${pegawaiId}][${newIndex}][volume]"
                        value="${volume}"
                        class="form-control volume-field">
                </td>
                <td>
                    <input type="text"
                        name="rincian[${pegawaiId}][${newIndex}][satuan]"
                        value="${satuan}"
                        class="form-control">
                </td>
                <td>
                    <input type="number"
                        name="rincian[${pegawaiId}][${newIndex}][tarif]"
                        value="${tarif}"
                        class="form-control tarif-field">
                </td>
                <td>
                    <input type="number"
                        name="rincian[${pegawaiId}][${newIndex}][total]"
                        value="${total}"
                        class="form-control total-field"
                        readonly>
                </td>
                <td>
                    <button type="button"
                        class="btn btn-danger btn-sm"
                        onclick="this.closest('tr').remove()">
                        Hapus
                    </button>
                </td>
            </tr>
            `);
        });
    }

    alert('Berhasil disalin ke semua pegawai');
}
</script>

@endsection