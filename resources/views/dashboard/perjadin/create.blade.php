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

{{-- ================= SURAT PERJALANAN ================= --}}
<div class="card card-shadow mb-4">
    <div class="card-body">
        <h5 class="section-title mb-3">Surat Perjalanan</h5>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Tanggal ST <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_st" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Nomor ST <span class="text-danger">*</span></label>
                <input type="text" name="nomor_st" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label>Nomor SK</label>
            <input type="text" name="nomor_sk" class="form-control">
        </div>
    </div>
</div>

{{-- ================= PESERTA (PEGAWAI + NON PEGAWAI) ================= --}}
<div class="card card-shadow mb-4">
    <div class="card-body">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="section-title mb-0">Peserta Perjalanan</h5>
            <div>
                <button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="addPegawaiRow()">
                    + Tambah Pegawai
                </button>
                <button type="button" class="btn btn-outline-success btn-sm me-2" onclick="addNonPegawaiRow()">
                    + Tambah Non-Pegawai
                </button>
                <button type="button" class="btn btn-outline-info btn-sm" onclick="copyToAllPeserta()" id="btnCopyToAll" style="display: none;">
                    📋 Copy ke Semua Peserta
                </button>
            </div>
        </div>

        <div id="pesertaContainer">
            {{-- Pegawai akan ditambahkan via JS --}}
            {{-- Non-pegawai akan ditambahkan via JS --}}
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

    document.addEventListener("DOMContentLoaded", function () {
        // Default tambah 1 baris pegawai
        addPegawaiRow();
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

    // Function untuk mengecek dan menampilkan/sembunyikan tombol Copy to All
    function toggleCopyToAllButton() {
        const container = document.getElementById('pesertaContainer');
        const pesertaCards = container.querySelectorAll('.peserta-card');
        const btnCopyToAll = document.getElementById('btnCopyToAll');
        
        if (btnCopyToAll) {
            btnCopyToAll.style.display = pesertaCards.length >= 2 ? 'inline-flex' : 'none';
        }
    }

    // Function untuk menyalin rincian dari peserta pertama ke semua peserta
    function copyToAllPeserta() {
        const container = document.getElementById('pesertaContainer');
        const pesertaCards = container.querySelectorAll('.peserta-card');
        
        if (pesertaCards.length < 2) {
            alert('Minimal ada 2 peserta untuk melakukan copy');
            return;
        }
        
        // Ambil peserta pertama sebagai sumber
        const firstPeserta = pesertaCards[0];
        const firstTbody = firstPeserta.querySelector('tbody');
        
        if (!firstTbody || firstTbody.querySelectorAll('tr').length === 0) {
            alert('Peserta pertama belum memiliki rincian biaya. Silakan tambah rincian terlebih dahulu.');
            return;
        }
        
        const firstRows = firstTbody.querySelectorAll('tr');
        let copiedCount = 0;
        
        // Loop mulai dari peserta ke-2 (indeks 1)
        for (let i = 1; i < pesertaCards.length; i++) {
            const targetPeserta = pesertaCards[i];
            const targetTbody = targetPeserta.querySelector('tbody');
            const targetPesertaId = targetPeserta.getAttribute('data-peserta-id');
            
            if (!targetTbody) continue;
            
            // Kosongkan tbody target terlebih dahulu
            targetTbody.innerHTML = '';
            
            // Clone setiap row dari peserta pertama
            firstRows.forEach(row => {
                const cloneRow = row.cloneNode(true);
                
                // Update semua name attribute dengan pesertaId target
                const allInputsSelects = cloneRow.querySelectorAll('input, select');
                allInputsSelects.forEach(field => {
                    const name = field.getAttribute('name');
                    if (name) {
                        // Ganti pesertaId di name attribute: rincian[LAMA][index][field] -> rincian[BARU][index][field]
                        const newName = name.replace(/rincian\[[^\]]+\]/, `rincian[${targetPesertaId}]`);
                        field.setAttribute('name', newName);
                    }
                });
                
                targetTbody.appendChild(cloneRow);
            });
            
            copiedCount++;
        }
        
        alert(`Berhasil menyalin rincian ke ${copiedCount} peserta lainnya.`);
    }

    function addPegawaiRow() {
        const container = document.getElementById('pesertaContainer');
        const pesertaId = `pegawai_${Date.now()}_${pesertaCounter++}`;
        
        let pegawaiOptions = '<option value="">Pilih Pegawai</option>';
        pegawaiData.forEach(p => {
            pegawaiOptions += `<option value="${p.id}">${p.nama} - ${p.jabatan} (${p.nip})</option>`;
        });

        container.insertAdjacentHTML('beforeend', `
            <div class="peserta-card border rounded p-3 mb-3" data-peserta-id="${pesertaId}" data-tipe="pegawai">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="fw-bold text-primary mb-0">👔 Peserta: Pegawai</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePeserta(this)">Hapus</button>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Pilih Pegawai <span class="text-danger">*</span></label>
                        <select name="peserta[${pesertaId}][pegawai_id]" class="form-select" required>
                            ${pegawaiOptions}
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-2">
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addRincianToPeserta('${pesertaId}')">
                        + Tambah Rincian Biaya
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" style="min-width: 1000px;">
                        <thead>
                            <tr><th>Jenis</th><th>Uraian</th><th>Volume</th><th>Satuan</th><th>Tarif</th><th>Total</th><th>Aksi</th></tr>
                        </thead>
                        <tbody id="tbody-${pesertaId}"></tbody>
                    </table>
                </div>
            </div>
        `);
        
        toggleCopyToAllButton();
    }

    function addNonPegawaiRow() {
        const container = document.getElementById('pesertaContainer');
        const pesertaId = `nonpegawai_${Date.now()}_${pesertaCounter++}`;

        container.insertAdjacentHTML('beforeend', `
            <div class="peserta-card border rounded p-3 mb-3" data-peserta-id="${pesertaId}" data-tipe="nonpegawai">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="fw-bold text-success mb-0">👤 Peserta: Non-Pegawai</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePeserta(this)">Hapus</button>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="peserta[${pesertaId}][nama]" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>NIK / Identitas</label>
                        <input type="text" name="peserta[${pesertaId}][nik]" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>Jabatan / Instansi</label>
                        <input type="text" name="peserta[${pesertaId}][instansi]" class="form-control">
                    </div>
                </div>

                <div class="d-flex gap-2 mb-2">
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addRincianToPeserta('${pesertaId}')">
                        + Tambah Rincian Biaya
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyRincianFromFirst('${pesertaId}')">
                        📋 Copy dari Peserta Pertama
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" style="min-width: 1000px;">
                        <thead>
                            <tr><th>Jenis</th><th>Uraian</th><th>Volume</th><th>Satuan</th><th>Tarif</th><th>Total</th><th>Aksi</th></tr>
                        </thead>
                        <tbody id="tbody-${pesertaId}"></tbody>
                    </table>
                </div>
            </div>
        `);
        
        toggleCopyToAllButton();
    }
    
    // Function untuk menyalin rincian dari peserta pertama ke peserta tertentu
    function copyRincianFromFirst(targetPesertaId) {
        const container = document.getElementById('pesertaContainer');
        const pesertaCards = container.querySelectorAll('.peserta-card');
        
        if (pesertaCards.length < 2) {
            alert('Tidak ada peserta lain sebagai sumber. Minimal 2 peserta.');
            return;
        }
        
        const firstPeserta = pesertaCards[0];
        const firstTbody = firstPeserta.querySelector('tbody');
        
        if (!firstTbody || firstTbody.querySelectorAll('tr').length === 0) {
            alert('Peserta pertama belum memiliki rincian biaya. Silakan tambah rincian terlebih dahulu.');
            return;
        }
        
        const targetPeserta = document.querySelector(`.peserta-card[data-peserta-id="${targetPesertaId}"]`);
        const targetTbody = targetPeserta.querySelector('tbody');
        
        if (!targetTbody) return;
        
        const firstRows = firstTbody.querySelectorAll('tr');
        
        // Kosongkan tbody target
        targetTbody.innerHTML = '';
        
        // Clone setiap row
        firstRows.forEach(row => {
            const cloneRow = row.cloneNode(true);
            
            const allInputsSelects = cloneRow.querySelectorAll('input, select');
            allInputsSelects.forEach(field => {
                const name = field.getAttribute('name');
                if (name) {
                    const newName = name.replace(/rincian\[[^\]]+\]/, `rincian[${targetPesertaId}]`);
                    field.setAttribute('name', newName);
                }
            });
            
            targetTbody.appendChild(cloneRow);
        });
        
        alert('Berhasil menyalin rincian dari peserta pertama.');
    }

    function addRincianToPeserta(pesertaId) {
        const tbody = document.getElementById(`tbody-${pesertaId}`);
        if (!tbody) return;
        
        const index = Date.now();
        const jumlahHari = getJumlahHari();
        
        let jenisOptions = '';
        jenisBiayaData.forEach(jenis => {
            jenisOptions += `<option value="${jenis.id}">${jenis.nama_biaya}</option>`;
        });

        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td><select name="rincian[${pesertaId}][${index}][jenis_biaya_id]" class="form-select">${jenisOptions}</select></td>
                <td><input type="text" name="rincian[${pesertaId}][${index}][uraian]" class="form-control" placeholder="Uraian biaya"></td>
                <td><input type="number" name="rincian[${pesertaId}][${index}][volume]" class="form-control volume-field" value="${jumlahHari}"></td>
                <td><input type="text" name="rincian[${pesertaId}][${index}][satuan]" class="form-control" value="hari"></td>
                <td><input type="number" name="rincian[${pesertaId}][${index}][tarif]" class="form-control tarif-field"></td>
                <td><input type="number" name="rincian[${pesertaId}][${index}][total]" class="form-control total-field" readonly></td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">Hapus</button></td>
            </tr>
        `);
    }

    function removePeserta(btn) {
        btn.closest('.peserta-card').remove();
        toggleCopyToAllButton();
    }

    // Event listener untuk perhitungan total
    document.addEventListener('input', function(e){
        if (e.target.classList.contains('volume-field') || e.target.classList.contains('tarif-field')) {
            const row = e.target.closest('tr');
            const volume = parseFloat(row.querySelector('.volume-field')?.value) || 0;
            const tarif = parseFloat(row.querySelector('.tarif-field')?.value) || 0;
            const totalField = row.querySelector('.total-field');
            if (totalField) totalField.value = (volume * tarif).toFixed(2);
        }
    });

    function updateSemuaVolume() {
        const jumlahHari = getJumlahHari();
        document.querySelectorAll('.volume-field').forEach(function(field){
            field.value = jumlahHari;
            const row = field.closest('tr');
            const tarif = parseFloat(row.querySelector('.tarif-field')?.value) || 0;
            const totalField = row.querySelector('.total-field');
            if (totalField) totalField.value = (jumlahHari * tarif).toFixed(2);
        });
    }

    document.getElementById('tanggal_mulai').addEventListener('change', updateSemuaVolume);
    document.getElementById('tanggal_akhir').addEventListener('change', updateSemuaVolume);
</script>

<style>
.table-responsive { overflow-x: auto; }
.table-responsive table { min-width: 950px; width: max-content; }
.table th, .table td { white-space: nowrap; }
.table th:nth-child(2), .table td:nth-child(2) { min-width: 280px; }
</style>

@endsection;