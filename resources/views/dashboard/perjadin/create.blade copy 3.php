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
.peserta-card {
    transition: all 0.2s;
}
.peserta-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
                <input type="text" name="alat_angkutan" class="form-control" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Dari Kota <span class="text-danger">*</span></label>
                <input type="text" name="dari_kota" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Tujuan Kota <span class="text-danger">*</span></label>
                <input type="text" name="tujuan_kota" class="form-control" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Tanggal Akhir <span class="text-danger">*</span></label>
                <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Tanggal Terima <span class="text-danger">*</span></label>
                <input type="date" id="tanggal_terima" name="tanggal_terima" class="form-control" required>
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
            <textarea name="nama_kegiatan" class="form-control" rows="3" required></textarea>
        </div>
    </div>
</div>

{{-- ================= PESERTA (TAB KELOMPOK) ================= --}}
<div class="card card-shadow mb-4">
    <div class="card-body">

        <h5 class="section-title mb-3">Peserta Perjalanan</h5>

        {{-- NAV TAB --}}
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#panitia">
                    Panitia
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#peserta">
                    Peserta
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#narasumber">
                    Narasumber
                </button>
            </li>
        </ul>

        <div class="tab-content">

            {{-- ================= PANITIA ================= --}}
            <div class="tab-pane fade show active" id="panitia">
                @include('dashboard.perjadin.partials.kelompok', ['key' => 'panitia', 'label' => 'Panitia', 'tipe' => 'pegawai'])
            </div>

            {{-- ================= PESERTA ================= --}}
            <div class="tab-pane fade" id="peserta">
                @include('dashboard.perjadin.partials.kelompok', ['key' => 'peserta', 'label' => 'Peserta', 'tipe' => 'pegawai'])
            </div>

            {{-- ================= NARASUMBER ================= --}}
            <div class="tab-pane fade" id="narasumber">
                @include('dashboard.perjadin.partials.kelompok', ['key' => 'narasumber', 'label' => 'Narasumber', 'tipe' => 'nonpegawai'])
            </div>

        </div>

    </div>
</div>

<div class="text-end mb-5">
    <button class="btn btn-success px-4">Simpan</button>
</div>

</form>

<script>
const jenisBiayaData = @json($jenisBiaya);
const pegawaiData = @json($pegawai);
let pesertaCounter = 0;

function getJumlahHari() {
    const mulai = document.getElementById('tanggal_mulai').value;
    const akhir = document.getElementById('tanggal_akhir').value;
    if (!mulai || !akhir) return 0;
    const selisih = (new Date(akhir) - new Date(mulai)) / (1000 * 60 * 60 * 24);
    return selisih >= 0 ? selisih + 1 : 0;
}

let subKelompokCounter = 1;

function addSubKelompok(kelompok) {

    const container = document.getElementById(`container-${kelompok}`);

    const index = subKelompokCounter++;

    container.insertAdjacentHTML('beforeend', `
        <div class="card border mb-3">
            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-5">
                        <label>Nomor ST</label>
                        <input type="text" 
                            name="kelompok[${kelompok}][${index}][nomor_st]" 
                            class="form-control">
                    </div>

                    <div class="col-md-5">
                        <label>Tanggal ST</label>
                        <input type="date" 
                            name="kelompok[${kelompok}][${index}][tanggal_st]" 
                            class="form-control">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" 
                            class="btn btn-outline-danger w-100"
                            onclick="this.closest('.card').remove()">
                            Hapus
                        </button>
                    </div>
                </div>

                <div id="subkelompok-${kelompok}-${index}"></div>

                <button type="button" 
                    class="btn btn-sm btn-outline-success mt-2"
                    onclick="addPegawaiRow('${kelompok}', ${index})">
                    + Pegawai
                </button>

                <button type="button" 
                    class="btn btn-sm btn-outline-success mt-2"
                    onclick="addNonPegawaiRow('${kelompok}', ${index})">
                    + Non Pegawai
                </button>

            </div>
        </div>
    `);
}

function addPegawaiRow(kelompok, subIndex) {
    const container = document.getElementById(`subkelompok-${kelompok}-${subIndex}`);
    const pesertaId = `${kelompok}_${subIndex}_${Date.now()}`;

    let options = '<option value="">Pilih Pegawai</option>';
    pegawaiData.forEach(p => {
        options += `<option value="${p.id}">${p.nama} (${p.nip})</option>`;
    });

    container.insertAdjacentHTML('beforeend', `
        <div class="peserta-card card mb-3 border" data-peserta-id="${pesertaId}">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <strong class="text-primary">
                    <i class="bi bi-person-badge me-1"></i> ${kelompok.toUpperCase()} - Pegawai
                </strong>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.peserta-card').remove()">
                    <i class="bi bi-trash me-1"></i> Hapus
                </button>
            </div>
            <div class="card-body">
                <select name="peserta[${kelompok}][${subIndex}][${pesertaId}][pegawai_id]" class="form-select mb-3">
                    ${options}
                </select>
                ${renderTable(kelompok, pesertaId, subIndex)}
            </div>
        </div>
    `);

    toggleCopyToAllButton(kelompok);
}

function addNonPegawaiRow(kelompok, subIndex) {
    const container = document.getElementById(`subkelompok-${kelompok}-${subIndex}`);
    const pesertaId = `${kelompok}_${subIndex}_${Date.now()}`;
    
    // const container = document.getElementById(`container-${kelompok}`);
    // const pesertaId = `${kelompok}_${Date.now()}_${pesertaCounter++}`;

    container.insertAdjacentHTML('beforeend', `
        <div class="peserta-card border rounded p-3 mb-3" data-peserta-id="${pesertaId}">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <strong class="text-success">
                    <i class="bi bi-person me-1"></i> ${kelompok.toUpperCase()} - Non Pegawai
                </strong>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.peserta-card').remove()">
                    <i class="bi bi-trash me-1"></i> Hapus
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small text-secondary fw-semibold">Nama Lengkap</label>
                        <input type="text"
                            name="peserta[${kelompok}][${subIndex}][${pesertaId}][nama]"
                            class="form-control"
                            placeholder="Masukkan nama lengkap">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-secondary fw-semibold">NIP / NIK</label>
                        <input type="text"
                            name="peserta[${kelompok}][${subIndex}][${pesertaId}][nik]"
                            class="form-control"
                            placeholder="Masukkan NIP / NIK">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-secondary fw-semibold">Instansi / Jabatan</label>
                        <input type="text"
                            name="peserta[${kelompok}][${subIndex}][${pesertaId}][instansi]"
                            class="form-control"
                            placeholder="Masukkan instansi atau jabatan">
                    </div>
                </div>
                ${renderTable(kelompok, pesertaId, subIndex)}
            </div>
        </div>
    `);

    toggleCopyToAllButton(kelompok);
}

function renderTable(kelompok, pesertaId, subIndex) {
    return `
        <div class="mt-3">
            <button type="button"
                class="btn btn-sm btn-outline-success mb-3"
                onclick="addRincian('${kelompok}','${pesertaId}', ${subIndex})">
                + Tambah Rincian
            </button>

            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th style="width:15%">Jenis</th>
                            <th style="width:25%">Uraian</th>
                            <th style="width:10%">Vol</th>
                            <th style="width:10%">Satuan</th>
                            <th style="width:15%">Tarif</th>
                            <th style="width:15%">Total</th>
                            <th style="width:10%"></th>
                        </tr>
                    </thead>
                    <tbody id="tbody-${pesertaId}"></tbody>
                </table>
            </div>
        </div>
    `;
}

function addRincian(kelompok, pesertaId, subIndex) {
    const tbody = document.getElementById(`tbody-${pesertaId}`);
    const index = Date.now();
    const hari = getJumlahHari();

    // ✅ WAJIB ADA
    let jenisOptions = '<option value="">Pilih Jenis Biaya</option>';
    jenisBiayaData.forEach(j => {
        jenisOptions += `<option value="${j.id}">${j.nama_biaya}</option>`;
    });

    tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <td>
                <select name="rincian[${kelompok}][${subIndex}][${pesertaId}][${index}][jenis_biaya_id]" 
                        class="form-select form-select-sm">
                    ${jenisOptions}
                </select>
            </td>
            <td>
                <input type="text" 
                    name="rincian[${kelompok}][${subIndex}][${pesertaId}][${index}][uraian]" 
                    class="form-control form-control-sm">
            </td>
            <td>
                <input type="number" 
                    name="rincian[${kelompok}][${subIndex}][${pesertaId}][${index}][volume]" 
                    class="form-control form-control-sm vol" 
                    value="${hari}">
            </td>
            <td>
                <input type="text" 
                    name="rincian[${kelompok}][${subIndex}][${pesertaId}][${index}][satuan]" 
                    class="form-control form-control-sm" 
                    value="hari">
            </td>
            <td>
                <input type="number" 
                    name="rincian[${kelompok}][${subIndex}][${pesertaId}][${index}][tarif]" 
                    class="form-control form-control-sm tarif">
            </td>
            <td>
                <input type="number" 
                    name="rincian[${kelompok}][${subIndex}][${pesertaId}][${index}][total]" 
                    class="form-control form-control-sm total" readonly>
            </td>
        </tr>
    `);
}y

function toggleCopyToAllButton(kelompok) {
    const container = document.getElementById(`container-${kelompok}`);
    const pesertaCards = container.querySelectorAll('.peserta-card');
    const btn = document.getElementById(`btnCopy-${kelompok}`);

    if (btn) {
        btn.style.display = pesertaCards.length >= 2 ? 'inline-flex' : 'none';
    }
}

function copyToAllPeserta(kelompok) {
    const container = document.getElementById(`container-${kelompok}`);
    const pesertaCards = container.querySelectorAll('.peserta-card');

    if (pesertaCards.length < 2) {
        alert('Minimal ada 2 peserta');
        return;
    }

    const firstPeserta = pesertaCards[0];
    const firstTbody = firstPeserta.querySelector('tbody');

    if (!firstTbody || firstTbody.querySelectorAll('tr').length === 0) {
        alert('Peserta pertama belum ada rincian');
        return;
    }

    const firstRows = firstTbody.querySelectorAll('tr');
    let copied = 0;

    for (let i = 1; i < pesertaCards.length; i++) {
        const target = pesertaCards[i];
        const targetTbody = target.querySelector('tbody');

        if (!targetTbody) continue;

        targetTbody.innerHTML = '';

        firstRows.forEach(row => {
            const clone = row.cloneNode(true);

            const originalFields = row.querySelectorAll('input, select');
            const cloneFields = clone.querySelectorAll('input, select');

            cloneFields.forEach((field, index) => {
                const name = field.getAttribute('name');

                if (name) {
                    const newName = name.replace(
                        /rincian\[[^\]]+\]\[[^\]]+\]/,
                        `rincian[${kelompok}][${target.dataset.pesertaId}]`
                    );
                    field.setAttribute('name', newName);
                }

                // 🔥 FIX: copy value secara eksplisit
                if (field.tagName === 'SELECT') {
                    field.value = originalFields[index].value;
                } else {
                    field.value = originalFields[index].value;
                }
            });

            targetTbody.appendChild(clone);
        });

        copied++;
    }

    alert(`Berhasil copy ke ${copied} peserta`);
}
</script>

<style>
.table-responsive { overflow-x: auto; }
.table-responsive table { min-width: 950px; width: max-content; }
.table th, .table td { white-space: nowrap; }
.table th:nth-child(2), .table td:nth-child(2) { min-width: 280px; }
</style>

@endsection;