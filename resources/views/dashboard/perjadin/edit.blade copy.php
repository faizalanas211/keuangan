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
.peserta-card {
    transition: all 0.2s;
}
.peserta-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>

<form action="{{ route('perjadin.update', $perjalanan->id) }}" method="POST">
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
                    value="{{ old('tingkat_perjalanan', $perjalanan->tingkat_perjalanan) }}">
            </div>
            <div class="col-md-6">
                <label>Alat Angkutan <span class="text-danger">*</span></label>
                <input type="text" name="alat_angkutan" class="form-control"
                    value="{{ old('alat_angkutan', $perjalanan->alat_angkutan) }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Dari Kota <span class="text-danger">*</span></label>
                <input type="text" name="dari_kota" class="form-control"
                    value="{{ old('dari_kota', $perjalanan->dari_kota) }}" required>
            </div>
            <div class="col-md-6">
                <label>Tujuan Kota <span class="text-danger">*</span></label>
                <input type="text" name="tujuan_kota" class="form-control"
                    value="{{ old('tujuan_kota', $perjalanan->tujuan_kota) }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control"
                    value="{{ old('tanggal_mulai', \Carbon\Carbon::parse($perjalanan->tanggal_mulai)->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6">
                <label>Tanggal Akhir <span class="text-danger">*</span></label>
                <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control"
                    value="{{ old('tanggal_akhir', \Carbon\Carbon::parse($perjalanan->tanggal_akhir)->format('Y-m-d')) }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Tanggal Terima <span class="text-danger">*</span></label>
                <input type="date" id="tanggal_terima" name="tanggal_terima" class="form-control"
                    value="{{ old('tanggal_terima', $perjalanan->tanggal_terima ? \Carbon\Carbon::parse($perjalanan->tanggal_terima)->format('Y-m-d') : '') }}" required>
            </div>
            <div class="col-md-6">
                <label>Kode MAK</label>
                <input type="text" name="kode_mak" class="form-control"
                    value="{{ old('kode_mak', $perjalanan->kode_mak) }}">
            </div>
        </div>

        <div class="mb-3">
            <label>Output: Akun: Biaya Kegiatan dalam rangka komponen/subkomponen</label>
            <textarea name="akun_biaya" class="form-control" rows="3">{{ old('akun_biaya', $perjalanan->akun_biaya) }}</textarea>
        </div>

        <div class="mb-3">
            <label>Nama Kegiatan <span class="text-danger">*</span></label>
            <textarea name="nama_kegiatan" class="form-control" rows="3" required>{{ old('nama_kegiatan', $perjalanan->nama_kegiatan) }}</textarea>
        </div>
    </div>
</div>

{{-- ================= PESERTA (PEGAWAI + NON PEGAWAI) ================= --}}
{{-- ================= PESERTA (TAB KELOMPOK) ================= --}}
<div class="card card-shadow mb-4">
    <div class="card-body">

        <h5 class="section-title mb-3">Peserta Perjalanan</h5>

        {{-- NAV TAB --}}
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <button type="button" class="nav-link active"
                        data-bs-toggle="tab"
                        data-bs-target="#panitia">
                    Panitia
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#peserta">
                    Peserta
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#narasumber">
                    Narasumber
                </button>
            </li>
        </ul>

        <div class="tab-content">

            <div class="tab-pane fade show active" id="panitia">

                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary"
                            onclick="addPegawaiRow('panitia')">
                        + Pegawai
                    </button>
                </div>

                <div id="container-panitia"></div>
            </div>

            <div class="tab-pane fade" id="peserta">
                <button type="button" class="btn btn-sm btn-outline-primary"
                        onclick="addPegawaiRow('peserta')">
                    + Pegawai
                </button>

                <button type="button" class="btn btn-sm btn-outline-success"
                        onclick="addNonPegawaiRow('peserta')">
                    + Non Pegawai
                </button>

                <div id="container-peserta"></div>
            </div>

            <div class="tab-pane fade" id="narasumber">
                <button type="button" class="btn btn-sm btn-outline-success"
                        onclick="addNonPegawaiRow('narasumber')">
                    + Non Pegawai
                </button>

                <div id="container-narasumber"></div>
            </div>

        </div>

    </div>
</div>

<div class="text-end mb-5">
    <button class="btn btn-success px-4">Simpan Perubahan</button>
</div>

</form>

<script>
    const kelompokData = @json($kelompok);
    const jenisBiayaData = @json($jenisBiaya);
    const pegawaiData = @json($pegawai);
    
    let pesertaCounter = 0;

    document.addEventListener("DOMContentLoaded", function () {
        loadExistingPeserta();
    });

    function getJumlahHari() {
        const mulai = document.getElementById('tanggal_mulai').value;
        const akhir = document.getElementById('tanggal_akhir').value;
        if (!mulai || !akhir) return 0;
        const tglMulai = new Date(mulai);
        const tglAkhir = new Date(akhir);
        const selisih = (tglAkhir - tglMulai) / (1000 * 60 * 60 * 24);
        return selisih >= 0 ? selisih + 1 : 0;
    }

    function loadExistingPeserta() {
        console.log('kelompokData:', kelompokData);
        
        if (!kelompokData || kelompokData.length === 0) return;

        kelompokData.forEach(kelompok => {
            const key = kelompok.jenis; // panitia / peserta / narasumber
            
            console.log(`Loading kelompok: ${key}`);

            // PEGAWAI
            if (kelompok.pegawai && kelompok.pegawai.length > 0) {
                console.log(`Pegawai count: ${kelompok.pegawai.length}`);
                kelompok.pegawai.forEach(p => {
                    addPegawaiRowFromExisting(key, p);
                });
            }

            // NON PEGAWAI
            if (kelompok.nonpegawai && kelompok.nonpegawai.length > 0) {
                console.log(`Nonpegawai count: ${kelompok.nonpegawai.length}`);
                kelompok.nonpegawai.forEach(np => {
                    addNonPegawaiRowFromExisting(key, np);
                });
            }

            toggleCopyToAllButton(key);
        });
    }

    function addNonPegawaiRow(kelompok) {
    const container = document.getElementById(`container-${kelompok}`);
    if (!container) return;

    const pesertaId = `${kelompok}_nonpegawai_baru_${Date.now()}_${pesertaCounter++}`;

    const html = `
        <div class="peserta-card border rounded p-3 mb-3" data-peserta-id="${pesertaId}">

            <div class="d-flex justify-content-between mb-2">
                <strong class="text-success">Non Pegawai (Baru)</strong>
                <button type="button" class="btn btn-sm btn-danger"
                        onclick="this.closest('.peserta-card').remove()">
                    Hapus
                </button>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <input type="text" name="peserta[${kelompok}][${pesertaId}][nama]" 
                           class="form-control" placeholder="Nama">
                </div>
                <div class="col-md-4">
                    <input type="text" name="peserta[${kelompok}][${pesertaId}][nik]" 
                           class="form-control" placeholder="NIK">
                </div>
                <div class="col-md-4">
                    <input type="text" name="peserta[${kelompok}][${pesertaId}][instansi]" 
                           class="form-control" placeholder="Instansi">
                </div>
            </div>

            <button type="button" class="btn btn-sm btn-outline-success mb-2"
                    onclick="addRincianToPeserta('${kelompok}', '${pesertaId}')">
                + Rincian
            </button>

            <table class="table table-sm table-bordered">
                <tbody id="tbody-${pesertaId}"></tbody>
            </table>

        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
}

    function addPegawaiRow(kelompok) {
        const container = document.getElementById(`container-${kelompok}`);
        if (!container) return;

        const pesertaId = `${kelompok}_pegawai_baru_${Date.now()}_${pesertaCounter++}`;

        let options = '<option value="">Pilih Pegawai</option>';
        pegawaiData.forEach(p => {
            options += `<option value="${p.id}">${p.nama} (${p.nip})</option>`;
        });

        const html = `
            <div class="peserta-card border rounded p-3 mb-3" data-peserta-id="${pesertaId}">
                
                <div class="d-flex justify-content-between mb-2">
                    <strong class="text-primary">Pegawai (Baru)</strong>
                    <button type="button" class="btn btn-sm btn-danger"
                            onclick="this.closest('.peserta-card').remove()">
                        Hapus
                    </button>
                </div>

                <select name="peserta[${kelompok}][${pesertaId}][pegawai_id]" class="form-select mb-2">
                    ${options}
                </select>

                <button type="button" class="btn btn-sm btn-outline-success mb-2"
                        onclick="addRincianToPeserta('${kelompok}', '${pesertaId}')">
                    + Rincian
                </button>

                <table class="table table-sm table-bordered">
                    <tbody id="tbody-${pesertaId}"></tbody>
                </table>

            </div>
        `;

        container.insertAdjacentHTML('beforeend', html);
    }

    function addPegawaiRowFromExisting(kelompok, data) {
        const container = document.getElementById(`container-${kelompok}`);
        if (!container) {
            console.error(`Container container-${kelompok} not found`);
            return;
        }
        
        const pesertaId = data.peserta_id || `${kelompok}_pegawai_${data.id}_${Date.now()}`;

        let options = '<option value="">Pilih Pegawai</option>';
        pegawaiData.forEach(p => {
            const selected = (p.id == data.pegawai_id || p.id == data.id) ? 'selected' : '';
            options += `<option value="${p.id}" ${selected}>${p.nama} (${p.nip})</option>`;
        });

        // Build rincian rows
        let rows = '';
        if (data.rincian && data.rincian.length > 0) {
            data.rincian.forEach((r, idx) => {
                const randomIdx = Date.now() + idx;
                rows += `
                <tr>
                    <td>
                        <select name="rincian[${kelompok}][${pesertaId}][${randomIdx}][jenis_biaya_id]" class="form-select form-select-sm">
                            ${jenisBiayaData.map(j => `
                                <option value="${j.id}" ${j.id == r.jenis_biaya_id ? 'selected' : ''}>
                                    ${j.nama_biaya}
                                </option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="text" name="rincian[${kelompok}][${pesertaId}][${randomIdx}][uraian]" 
                               class="form-control form-control-sm" value="${r.uraian || ''}">
                    </td>
                    <td>
                        <input type="number" name="rincian[${kelompok}][${pesertaId}][${randomIdx}][volume]" 
                               class="form-control form-control-sm volume-field" value="${r.volume || 0}">
                    </td>
                    <td>
                        <input type="text" name="rincian[${kelompok}][${pesertaId}][${randomIdx}][satuan]" 
                               class="form-control form-control-sm" value="${r.satuan || ''}">
                    </td>
                    <td>
                        <input type="number" name="rincian[${kelompok}][${pesertaId}][${randomIdx}][tarif]" 
                               class="form-control form-control-sm tarif-field" value="${r.tarif || 0}">
                    </td>
                    <td>
                        <input type="number" name="rincian[${kelompok}][${pesertaId}][${randomIdx}][total]" 
                               class="form-control form-control-sm total-field" value="${r.total || 0}" readonly>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </td>
                </tr>
                `;
            });
        }

        const html = `
            <div class="peserta-card border rounded p-3 mb-3" data-peserta-id="${pesertaId}" data-tipe="pegawai">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="bi bi-person-badge me-1"></i> Pegawai
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.peserta-card').remove()">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </button>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold small">Pilih Pegawai</label>
                        <select name="peserta[${kelompok}][${pesertaId}][pegawai_id]" class="form-select" required>
                            ${options}
                        </select>
                        <input type="hidden" name="peserta[${kelompok}][${pesertaId}][perjalanan_dinas_pegawai_id]" value="${data.perjalanan_dinas_pegawai_id || ''}">
                    </div>
                </div>

                <div class="d-flex gap-2 mb-2">
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addRincianToPeserta('${kelompok}', '${pesertaId}')">
                        <i class="bi bi-plus-circle me-1"></i> + Rincian Biaya
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="width:20%">Jenis</th>
                                <th style="width:25%">Uraian</th>
                                <th style="width:10%">Volume</th>
                                <th style="width:10%">Satuan</th>
                                <th style="width:15%">Tarif</th>
                                <th style="width:15%">Total</th>
                                <th style="width:5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-${pesertaId}">
                            ${rows}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', html);
        console.log(`Added pegawai row for ${kelompok} with pesertaId: ${pesertaId}`);
    }

    function addNonPegawaiRowFromExisting(kelompok, data) {
        const container = document.getElementById(`container-${kelompok}`);
        if (!container) {
            console.error(`Container container-${kelompok} not found`);
            return;
        }
        
        const pesertaId = data.peserta_id || `${kelompok}_nonpegawai_${data.id}_${Date.now()}`;

        // Build rincian rows
        let rows = '';
        if (data.rincian && data.rincian.length > 0) {
            data.rincian.forEach((r, idx) => {
                const randomIdx = Date.now() + idx;
                rows += `
                <tr>
                    <td>
                        <select name="rincian[${kelompok}][${pesertaId}][${randomIdx}][jenis_biaya_id]" class="form-select form-select-sm">
                            ${jenisBiayaData.map(j => `
                                <option value="${j.id}" ${j.id == r.jenis_biaya_id ? 'selected' : ''}>
                                    ${j.nama_biaya}
                                </option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="text" name="rincian[${kelompok}][${pesertaId}][${randomIdx}][uraian]" 
                               class="form-control form-control-sm" value="${r.uraian || ''}">
                    </td>
                    <td>
                        <input type="number" name="rincian[${kelompok}][${pesertaId}][${randomIdx}][volume]" 
                               class="form-control form-control-sm volume-field" value="${r.volume || 0}">
                    </td>
                    <td>
                        <input type="text" name="rincian[${kelompok}][${pesertaId}][${randomIdx}][satuan]" 
                               class="form-control form-control-sm" value="${r.satuan || ''}">
                    </td>
                    <td>
                        <input type="number" name="rincian[${kelompok}][${pesertaId}][${randomIdx}][tarif]" 
                               class="form-control form-control-sm tarif-field" value="${r.tarif || 0}">
                    </td>
                    <td>
                        <input type="number" name="rincian[${kelompok}][${pesertaId}][${randomIdx}][total]" 
                               class="form-control form-control-sm total-field" value="${r.total || 0}" readonly>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </td>
                </tr>
                `;
            });
        }

        const html = `
            <div class="peserta-card border rounded p-3 mb-3" data-peserta-id="${pesertaId}" data-tipe="nonpegawai">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="fw-bold text-success mb-0">
                        <i class="bi bi-person me-1"></i> Non Pegawai
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.peserta-card').remove()">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </button>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Nama Lengkap</label>
                        <input type="text" name="peserta[${kelompok}][${pesertaId}][nama]" 
                               class="form-control" value="${data.nama || ''}" required>
                        <input type="hidden" name="peserta[${kelompok}][${pesertaId}][nonpegawai_id]" value="${data.nonpegawai_id || ''}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">NIK / Identitas</label>
                        <input type="text" name="peserta[${kelompok}][${pesertaId}][nik]" 
                               class="form-control" value="${data.nik || ''}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Jabatan / Instansi</label>
                        <input type="text" name="peserta[${kelompok}][${pesertaId}][instansi]" 
                               class="form-control" value="${data.instansi || ''}">
                    </div>
                </div>

                <div class="d-flex gap-2 mb-2">
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addRincianToPeserta('${kelompok}', '${pesertaId}')">
                        <i class="bi bi-plus-circle me-1"></i> + Rincian Biaya
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="width:20%">Jenis</th>
                                <th style="width:25%">Uraian</th>
                                <th style="width:10%">Volume</th>
                                <th style="width:10%">Satuan</th>
                                <th style="width:15%">Tarif</th>
                                <th style="width:15%">Total</th>
                                <th style="width:5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-${pesertaId}">
                            ${rows}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', html);
        console.log(`Added nonpegawai row for ${kelompok} with pesertaId: ${pesertaId}`);
    }

    function addRincianToPeserta(kelompok, pesertaId) {
        const tbody = document.getElementById(`tbody-${pesertaId}`);
        if (!tbody) return;
        
        const index = Date.now();
        const jumlahHari = getJumlahHari();
        
        let jenisOptions = '<option value="">Pilih Jenis Biaya</option>';
        jenisBiayaData.forEach(jenis => {
            jenisOptions += `<option value="${jenis.id}">${jenis.nama_biaya}</option>`;
        });

        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td>
                    <select name="rincian[${kelompok}][${pesertaId}][${index}][jenis_biaya_id]" class="form-select form-select-sm">
                        ${jenisOptions}
                    </select>
                </td>
                <td>
                    <input type="text" name="rincian[${kelompok}][${pesertaId}][${index}][uraian]" 
                           class="form-control form-control-sm" placeholder="Uraian biaya">
                </td>
                <td>
                    <input type="number" name="rincian[${kelompok}][${pesertaId}][${index}][volume]" 
                           class="form-control form-control-sm volume-field" value="${jumlahHari}">
                </td>
                <td>
                    <input type="text" name="rincian[${kelompok}][${pesertaId}][${index}][satuan]" 
                           class="form-control form-control-sm" value="hari">
                </td>
                <td>
                    <input type="number" name="rincian[${kelompok}][${pesertaId}][${index}][tarif]" 
                           class="form-control form-control-sm tarif-field">
                </td>
                <td>
                    <input type="number" name="rincian[${kelompok}][${pesertaId}][${index}][total]" 
                           class="form-control form-control-sm total-field" readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    }

    function toggleCopyToAllButton(kelompok) {
        const container = document.getElementById(`container-${kelompok}`);
        const pesertaCards = container.querySelectorAll('.peserta-card');
        const btnCopy = document.getElementById(`btnCopy-${kelompok}`);
        
        if (btnCopy) {
            btnCopy.style.display = pesertaCards.length >= 2 ? 'inline-flex' : 'none';
        }
    }

    // Event listener untuk perhitungan total
    document.addEventListener('input', function(e){
        if (e.target.classList.contains('volume-field') || e.target.classList.contains('tarif-field')) {
            const row = e.target.closest('tr');
            const volume = parseFloat(row.querySelector('.volume-field')?.value) || 0;
            const tarif = parseFloat(row.querySelector('.tarif-field')?.value) || 0;
            const totalField = row.querySelector('.total-field');
            if (totalField) totalField.value = volume * tarif;
        }
    });

    function updateSemuaVolume() {
        const jumlahHari = getJumlahHari();
        document.querySelectorAll('.volume-field').forEach(function(field){
            field.value = jumlahHari;
            const row = field.closest('tr');
            const tarif = parseFloat(row.querySelector('.tarif-field')?.value) || 0;
            const totalField = row.querySelector('.total-field');
            if (totalField) totalField.value = jumlahHari * tarif;
        });
    }

    document.getElementById('tanggal_mulai')?.addEventListener('change', updateSemuaVolume);
    document.getElementById('tanggal_akhir')?.addEventListener('change', updateSemuaVolume);
</script>

<style>
.table-responsive { overflow-x: auto; }
.table-responsive table { min-width: 950px; width: max-content; }
.table th, .table td { white-space: nowrap; }
.table th:nth-child(2), .table td:nth-child(2) { min-width: 280px; }
</style>

@endsection