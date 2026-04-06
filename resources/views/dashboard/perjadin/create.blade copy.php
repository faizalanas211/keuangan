@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('perjadin.index') }}">Data Perjalanan Dinas</a>
</li>
<li class="breadcrumb-item active text-success fw-semibold">
    Tambah Perjalanan Dinas
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

<form action="{{ route('perjadin.store') }}" method="POST">
@csrf

{{-- ================= INFORMASI ================= --}}
<div class="card card-shadow mb-4">
    <div class="card-body">

        <h5 class="section-title mb-3">Informasi Perjalanan</h5>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Tingkat Perjalanan</label>
                <input type="text" name="tingkat_perjalanan" class="form-control">
            </div>
            <div class="col-md-6">
                <label>Alat Angkutan</label>
                <input type="text" name="alat_angkutan" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Dari Kota</label>
                <input type="text" name="dari_kota" class="form-control">
            </div>
            <div class="col-md-6">
                <label>Tujuan Kota</label>
                <input type="text" name="tujuan_kota" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control">
            </div>
            <div class="col-md-6">
                <label>Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="form-control">
            </div>
        </div>

        <div class="mb-3">
            <label>Kode MAK</label>
            <input type="text" name="kode_mak" class="form-control">
        </div>

        <div class="mb-3">
            <label>Output: Akun: Biaya Kegiatan dalam rangka komponen/subkomponen</label>
            <textarea name="akun_biaya" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label>Nama Kegiatan</label>
            <textarea name="nama_kegiatan" class="form-control" rows="3"></textarea>
        </div>

    </div>
</div>

{{-- ================= SURAT PERJALANAN ================= --}}
<div class="card card-shadow mb-4">
    <div class="card-body">

        <h5 class="section-title mb-3">Surat Perjalanan</h5>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Nomor SK</label>
                <input type="text" 
                       name="nomor_sk" 
                       class="form-control">
            </div>

            <div class="col-md-6">
                <label>Nomor ST</label>
                <input type="text" 
                       name="nomor_st" 
                       class="form-control">
            </div>
        </div>

        <div class="mb-3">
            <label>Tanggal ST</label>
            <input type="date" 
                   name="tanggal_st" 
                   class="form-control">
        </div>

    </div>
</div>


{{-- ================= PEGAWAI ================= --}}
<div class="card card-shadow mb-4">
    <div class="card-body">

        <h5 class="section-title mb-3">Pilih Pegawai</h5>

        <div class="mb-3">
            <select name="pegawai[]" 
                    id="pegawaiSelect"
                    class="form-select" 
                    multiple>
                @foreach($pegawai as $p)
                    <option value="{{ $p->id }}">
                        {{ $p->nama }} - {{ $p->jabatan }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>
</div>

{{-- ================= RINCIAN ================= --}}
<div class="card card-shadow mb-4">
    <div class="card-body">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="section-title mb-0">Rincian Biaya</h5>
            <button type="button" class="btn btn-outline-success btn-sm"
                onclick="copyToAll()">
                Salin ke Semua Pegawai
            </button>
        </div>

        <div id="rincianContainer"></div>

    </div>
</div>

<div class="text-end mb-5">
    <button class="btn btn-success px-4">Simpan</button>
</div>

</form>

{{-- ================= SCRIPT ================= --}}

<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
    const jenisBiayaData = @json($jenisBiaya);
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const select = new TomSelect("#pegawaiSelect", {
        plugins: ['remove_button'],
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        },
        onChange: function (values) {
            generateRincian(values);
        }
    });

});

function generateRincian(selectedIds) {

    const container = document.getElementById('rincianContainer');
    container.innerHTML = '';

    selectedIds.forEach(function (pegawaiId) {

        const option = document.querySelector(
            '#pegawaiSelect option[value="' + pegawaiId + '"]'
        );

        const nama = option.text;

        let rows = '';

        jenisBiayaData.forEach(function(jenis){

            rows += `
                <tr>
                    <td>
                        ${jenis.nama_biaya}
                        <input type="hidden"
                            name="rincian[${pegawaiId}][${jenis.id}][jenis_biaya_id]"
                            value="${jenis.id}">
                    </td>
                    <td>
                        <input type="number" step="1.0"
                            name="rincian[${pegawaiId}][${jenis.id}][volume]"
                            class="form-control volume-field">
                    </td>
                    <td>
                        <input type="text"
                            name="rincian[${pegawaiId}][${jenis.id}][satuan]"
                            class="form-control">
                    </td>
                    <td>
                        <input type="number" step="0.01"
                            name="rincian[${pegawaiId}][${jenis.id}][tarif]"
                            class="form-control tarif-field">
                    </td>
                    <td>
                        <input type="number" step="1.0"
                            name="rincian[${pegawaiId}][${jenis.id}][total]"
                            class="form-control total-field"
                            readonly>
                    </td>
                </tr>
            `;
        });

        container.innerHTML += `
            <div class="border rounded p-3 mb-4">
                <h6 class="fw-bold text-success">${nama}</h6>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Jenis Biaya</th>
                            <th>Volume</th>
                            <th>Satuan</th>
                            <th>Tarif</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows}
                    </tbody>
                </table>
            </div>
        `;
    });
}

</script>
<script>

function calculateTotal(row) {
    const volume = parseFloat(row.querySelector('.volume-field')?.value) || 0;
    const tarif  = parseFloat(row.querySelector('.tarif-field')?.value) || 0;
    const totalField = row.querySelector('.total-field');

    totalField.value = (volume * tarif).toFixed(2);
}

document.addEventListener('input', function(e){

    if (e.target.classList.contains('volume-field') ||
        e.target.classList.contains('tarif-field')) {

        const row = e.target.closest('tr');
        calculateTotal(row);
    }
});

function copyToAll() {

    const allTables = document.querySelectorAll('#rincianContainer table');

    if (allTables.length <= 1) {
        alert('Minimal pilih 2 pegawai');
        return;
    }

    const firstTableRows = allTables[0].querySelectorAll('tbody tr');

    for (let i = 1; i < allTables.length; i++) {

        const targetRows = allTables[i].querySelectorAll('tbody tr');

        firstTableRows.forEach(function(sourceRow, index){

            const sourceVolume = sourceRow.querySelector('.volume-field').value;
            const sourceSatuan = sourceRow.querySelector('input[name*="[satuan]"]').value;
            const sourceTarif  = sourceRow.querySelector('.tarif-field').value;

            targetRows[index].querySelector('.volume-field').value = sourceVolume;
            targetRows[index].querySelector('input[name*="[satuan]"]').value = sourceSatuan;
            targetRows[index].querySelector('.tarif-field').value  = sourceTarif;

            calculateTotal(targetRows[index]);
        });
    }

    alert('Berhasil disalin ke semua pegawai');
}

</script>

@endsection
