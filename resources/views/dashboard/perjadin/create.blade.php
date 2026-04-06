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
                <label>Alat Angkutan <span class="text-danger">*</span></label>
                <input type="text" name="alat_angkutan" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Dari Kota <span class="text-danger">*</span></label>
                <input type="text" name="dari_kota" class="form-control">
            </div>
            <div class="col-md-6">
                <label>Tujuan Kota <span class="text-danger">*</span></label>
                <input type="text" name="tujuan_kota" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control">
            </div>
            <div class="col-md-6">
                <label>Tanggal Akhir <span class="text-danger">*</span></label>
                <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Tanggal Terima <span class="text-danger">*</span></label>
                <input type="date" id="tanggal_terima" name="tanggal_terima" class="form-control">
            </div>
            <div class="col-md-6">
                <label>Kode MAK</label>
                <input type="text" name="kode_mak" class="form-control">
            </div>
        </div>

        <div class="mb-3">
            <label>Output: Akun: Biaya Kegiatan dalam rangka komponen/subkomponen</label>
            <textarea name="akun_biaya" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label>Nama Kegiatan <span class="text-danger">*</span></label>
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
                <label>Tanggal ST <span class="text-danger">*</span></label>
                <input type="date" 
                    name="tanggal_st" 
                    class="form-control">
            </div>

            <div class="col-md-6">
                <label>Nomor ST <span class="text-danger">*</span></label>
                <input type="text" 
                       name="nomor_st" 
                       class="form-control">
            </div>
        </div>

        <div class="mb-3">
            <label>Nomor SK</label>
            <input type="text" 
                    name="nomor_sk" 
                    class="form-control">
        </div>

    </div>
</div>


{{-- ================= PEGAWAI ================= --}}
<div class="card card-shadow mb-4">
    <div class="card-body">

        <h5 class="section-title mb-3">Pilih Pegawai <span class="text-danger">*</span></h5>

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
            <h5 class="section-title mb-0">Rincian Biaya <span class="text-danger">*</span></h5>
            <button type="button"
                class="btn btn-outline-success btn-sm"
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
        placeholder: "Pilih satu pegawai atau lebih",
        sortField: {
            field: "text",
            direction: "asc"
        },
        onChange: function (values) {
            generateRincian(values);
        }
    });

});

function getJumlahHari() {
    const mulai = document.getElementById('tanggal_mulai').value;
    const akhir = document.getElementById('tanggal_akhir').value;

    if (!mulai || !akhir) return 0;

    const tglMulai = new Date(mulai);
    const tglAkhir = new Date(akhir);

    const selisih = (tglAkhir - tglMulai) / (1000 * 60 * 60 * 24);

    return selisih >= 0 ? selisih + 1 : 0; // inklusif
}

function generateRincian(selectedIds) {

    const container = document.getElementById('rincianContainer');

    const existing = Array.from(container.querySelectorAll('[data-pegawai]'))
        .map(div => div.getAttribute('data-pegawai'));

    // hapus pegawai yang di-unselect
    existing.forEach(id => {
        if (!selectedIds.includes(id)) {
            container.querySelector(`[data-pegawai="${id}"]`).remove();
        }
    });

    // tambah pegawai baru TANPA reset semua
    selectedIds.forEach(function (pegawaiId) {

        if (existing.includes(pegawaiId)) return;

        const option = document.querySelector(
            '#pegawaiSelect option[value="' + pegawaiId + '"]'
        );

        const nama = option.text;

        container.insertAdjacentHTML('beforeend', `
            <div class="border rounded p-3 mb-4" data-pegawai="${pegawaiId}">
                <h6 class="fw-bold text-success">${nama}</h6>

                <button type="button"
                    class="btn btn-sm btn-outline-success mb-2"
                    onclick="addRincianRow(${pegawaiId})">
                    + Tambah Rincian
                </button>

                <div class="table-responsive">
                    <table class="table table-bordered" style="min-width: 1000px;">
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
                    <tbody id="tbody-${pegawaiId}"></tbody>
                </table>
                </div>
            </div>
        `);
    });
}

function addRincianRow(pegawaiId) {

    const tbody = document.getElementById(`tbody-${pegawaiId}`);
    const index = Date.now();

    let jenisOptions = '';
    jenisBiayaData.forEach(jenis => {
        jenisOptions += `<option value="${jenis.id}">
                            ${jenis.nama_biaya}
                         </option>`;
    });

    const jumlahHari = getJumlahHari();

    tbody.insertAdjacentHTML('beforeend', `
    <tr>
        <td>
            <select name="rincian[${pegawaiId}][${index}][jenis_biaya_id]"
                class="form-select">
                ${jenisOptions}
            </select>
        </td>
        <td>
            <input type="text"
                name="rincian[${pegawaiId}][${index}][uraian]"
                class="form-control"
                placeholder="Contoh: Biaya taksi perjalanan dinas daerah">
        </td>
        <td>
            <input type="number"
                name="rincian[${pegawaiId}][${index}][volume]"
                class="form-control volume-field"
                value="${jumlahHari}"
                >
        </td>
        <td>
            <input type="text"
                name="rincian[${pegawaiId}][${index}][satuan]"
                class="form-control"
                value="hari"
                >
        </td>
        <td>
            <input type="number"
                name="rincian[${pegawaiId}][${index}][tarif]"
                class="form-control tarif-field">
        </td>
        <td>
            <input type="number"
                name="rincian[${pegawaiId}][${index}][total]"
                class="form-control total-field"
                readonly>
        </td>
        <td>
            <button type="button"
                class="btn btn-sm btn-danger"
                onclick="this.closest('tr').remove()">
                Hapus
            </button>
        </td>
    </tr>
    `);
}

function copyToAll() {

    const tables = document.querySelectorAll('#rincianContainer tbody');

    if (tables.length <= 1) {
        alert('Minimal pilih 2 pegawai');
        return;
    }

    const firstRows = tables[0].querySelectorAll('tr');

    for (let i = 1; i < tables.length; i++) {

        tables[i].innerHTML = '';

        firstRows.forEach(row => {

        const clone = row.cloneNode(true);
        const pegawaiId = tables[i].id.replace('tbody-', '');

        // ambil semua input & select di row asli
        const originalFields = row.querySelectorAll('input, select');
        const clonedFields   = clone.querySelectorAll('input, select');

        clonedFields.forEach((field, index) => {

            const original = originalFields[index];

            // 1️⃣ salin VALUE (ini yg bikin dropdown ikut ke-copy)
            field.value = original.value;

            // 2️⃣ ganti pegawaiId di name
            const name = field.getAttribute('name');
            const newName = name.replace(
                /rincian\[\d+\]/,
                `rincian[${pegawaiId}]`
            );

            field.setAttribute('name', newName);
        });

        tables[i].appendChild(clone);
    });
    }

    alert('Berhasil disalin ke semua pegawai');
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

function updateSemuaVolume() {
    const jumlahHari = getJumlahHari();

    document.querySelectorAll('.volume-field').forEach(function(field){
        field.value = jumlahHari;
        calculateTotal(field.closest('tr'));
    });
}

document.getElementById('tanggal_mulai')
    .addEventListener('change', updateSemuaVolume);

document.getElementById('tanggal_akhir')
    .addEventListener('change', updateSemuaVolume);
</script>

<style>
.table-responsive {
    overflow-x: auto;
}

.table-responsive table {
    min-width: 950px; /* bisa diturunin dari 1200 */
    width: max-content;
}

.table th,
.table td {
    white-space: nowrap;
}

/* Kolom Uraian (ke-2) lebih lebar */
.table th:nth-child(2),
.table td:nth-child(2) {
    min-width: 280px;
}

/* Volume (ke-3) kecil */
.table th:nth-child(3),
.table td:nth-child(3) {
    min-width: 80px;
    max-width: 100px;
}

/* Satuan (ke-4) kecil */
.table th:nth-child(4),
.table td:nth-child(4) {
    min-width: 90px;
    max-width: 110px;
}

/* Tarif (ke-5) sedang */
.table th:nth-child(5),
.table td:nth-child(5) {
    min-width: 120px;
    max-width: 140px;
}

/* Total (ke-6) sedang */
.table th:nth-child(6),
.table td:nth-child(6) {
    min-width: 120px;
}

/* Aksi (ke-7) kecil */
.table th:nth-child(7),
.table td:nth-child(7) {
    min-width: 70px;
}
</style>
@endsection
