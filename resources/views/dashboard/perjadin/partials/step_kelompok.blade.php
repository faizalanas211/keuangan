{{-- ================= PESERTA (TAB KELOMPOK) ================= --}}
<div>
    <div class="text-primary d-flex justify-content-between align-items-center">
        <h5 class="mb-0">👥 {{ $title }}</h5>

        <button type="button"
            class="btn btn-light btn-sm"
            onclick="addSubKelompok('{{ $tipe }}')">
            + Tambah ST
        </button>
    </div>

    <div class="card-body">
        <div id="container-{{ $tipe }}"></div>
    </div>
</div>

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

// Fungsi utama untuk menambah sub kelompok (tanpa auto-load anggota)
function addSubKelompok(kelompok, existingData = null) {
    const container = document.getElementById(`container-${kelompok}`);
    const index = subKelompokCounter++;

    container.insertAdjacentHTML('beforeend', `
        <div class="card border mb-3 st-card" data-st-index="${index}">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <div>
                    <strong>Sub Kelompok ST</strong>
                    <small class="text-muted d-block">Klik untuk buka detail peserta</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="button"
                        class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 toggle-btn"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse-${kelompok}-${index}">
                        Detail 
                        <i class="bi bi-chevron-down"></i>
                    </button>

                    <button type="button"
                        class="btn btn-sm btn-outline-danger"
                        onclick="this.closest('.st-card').remove()">
                        Hapus
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapse-${kelompok}-${index}">
                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Nomor ST</label>
                            <input type="text"
                                name="kelompok[${kelompok}][${index}][nomor_st]"
                                class="form-control"
                                value="${existingData?.nomor_st || ''}">
                        </div>

                        <div class="col-md-6">
                            <label>Tanggal ST</label>
                            <input type="date"
                                name="kelompok[${kelompok}][${index}][tanggal_st]"
                                class="form-control"
                                value="${existingData?.tanggal_st || ''}">
                        </div>
                    </div>

                    <div id="subkelompok-${kelompok}-${index}"></div>

                    <div class="d-flex gap-2">
                        <button type="button"
                            class="btn btn-sm btn-outline-success"
                            onclick="addPegawaiRow('${kelompok}', ${index})">
                            + Pegawai
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-outline-success"
                            onclick="addNonPegawaiRow('${kelompok}', ${index})">
                            + Non Pegawai
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-outline-info"
                            onclick="copyToAllPeserta('${kelompok}', ${index})">
                            <i class="bi bi-files"></i> Copy ke Semua
                        </button>
                    </div>

                </div>
            </div>
        </div>
    `);

    return index;
}

// Fungsi untuk load data edit (hanya dipanggil sekali dari edit.blade)
function loadEditData(kelompok, kelompokDataArray) {
    console.log(`loadEditData called for ${kelompok}:`, kelompokDataArray);
    
    if (!kelompokDataArray || kelompokDataArray.length === 0) return;
    
    // Bersihkan container
    const container = document.getElementById(`container-${kelompok}`);
    if (container) {
        container.innerHTML = '';
    }
    
    // Reset counter
    subKelompokCounter = 1;
    
    // Loop setiap kelompok data
    kelompokDataArray.forEach((kelompokData, idx) => {
        // Buat subkelompok tanpa data anggota dulu
        const stIndex = addSubKelompok(kelompok, {
            nomor_st: kelompokData.nomor_st || '',
            tanggal_st: kelompokData.tanggal_st || ''
        });
        
        // Ambil items (anggota)
        const items = kelompokData.items || [];
        
        // Tambahkan anggota satu per satu dengan delay
        items.forEach((anggota, anggotaIdx) => {
            setTimeout(() => {
                if (anggota.type === 'pegawai' || anggota.pegawai_id) {
                    // Tambah pegawai
                    const pesertaId = addPegawaiRow(kelompok, stIndex);
                    
                    // Isi data pegawai
                    const subKelompokDiv = document.getElementById(`subkelompok-${kelompok}-${stIndex}`);
                    const pesertaCard = subKelompokDiv.querySelector(`.peserta-card[data-peserta-id="${pesertaId}"]`);
                    
                    if (pesertaCard) {
                        const select = pesertaCard.querySelector('select');
                        if (select && (anggota.pegawai_id || anggota.id)) {
                            select.value = anggota.pegawai_id || anggota.id;
                        }
                        
                        // Load rincian
                        if (anggota.rincian && anggota.rincian.length > 0) {
                            loadRincianToPeserta(kelompok, pesertaId, stIndex, anggota.rincian);
                        }
                    }
                } else {
                    // Tambah non pegawai
                    const pesertaId = addNonPegawaiRow(kelompok, stIndex);
                    
                    // Isi data non pegawai
                    const subKelompokDiv = document.getElementById(`subkelompok-${kelompok}-${stIndex}`);
                    const pesertaCard = subKelompokDiv.querySelector(`.peserta-card[data-peserta-id="${pesertaId}"]`);
                    
                    if (pesertaCard) {
                        const namaInput = pesertaCard.querySelector('input[name*="[nama]"]');
                        const nikInput = pesertaCard.querySelector('input[name*="[nik]"]');
                        const instansiInput = pesertaCard.querySelector('input[name*="[instansi]"]');
                        
                        if (namaInput) namaInput.value = anggota.nama || '';
                        if (nikInput) nikInput.value = anggota.nik || '';
                        if (instansiInput) instansiInput.value = anggota.instansi || '';
                        
                        // Load rincian
                        if (anggota.rincian && anggota.rincian.length > 0) {
                            loadRincianToPeserta(kelompok, pesertaId, stIndex, anggota.rincian);
                        }
                    }
                }
            }, anggotaIdx * 200); // Delay 200ms per anggota
        });
    });
}

function addPegawaiRow(kelompok, subIndex) {
    const container = document.getElementById(`subkelompok-${kelompok}-${subIndex}`);
    const pesertaId = `${kelompok}_${subIndex}_${Date.now()}_${Math.random()}`;

    let options = '<option value="">Pilih Pegawai</option>';
    pegawaiData.forEach(p => {
        options += `<option value="${p.id}" data-nama="${p.nama}" data-nip="${p.nip}">${p.nama} (${p.nip})</option>`;
    });

    container.insertAdjacentHTML('beforeend', `
        <div class="peserta-card card mb-3 border" data-peserta-id="${pesertaId}" data-type="pegawai">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <strong class="text-primary">
                    <i class="bi bi-person-badge me-1"></i> ${kelompok.toUpperCase()} - Pegawai
                </strong>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.peserta-card').remove()">
                    <i class="bi bi-trash me-1"></i> Hapus
                </button>
            </div>
            <div class="card-body">
                <select name="peserta[${kelompok}][${subIndex}][${pesertaId}][pegawai_id]" 
                        class="form-select mb-3">
                    ${options}
                </select>
                ${renderTable(kelompok, pesertaId, subIndex)}
            </div>
        </div>
    `);

    toggleCopyToAllButton(kelompok);
    return pesertaId; 
}

function addNonPegawaiRow(kelompok, subIndex) {
    const container = document.getElementById(`subkelompok-${kelompok}-${subIndex}`);
    const pesertaId = `${kelompok}_${subIndex}_${Date.now()}_${Math.random()}`;

    container.insertAdjacentHTML('beforeend', `
        <div class="peserta-card border rounded p-3 mb-3" data-peserta-id="${pesertaId}" data-type="nonpegawai">
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
    return pesertaId; 
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
    const index = Date.now() + Math.random();
    const hari = getJumlahHari();

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
            <td class="text-center">
                <button type="button" 
                        class="btn btn-sm btn-outline-danger" 
                        onclick="this.closest('tr').remove()">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `);
    
    // Auto-calculate total
    const row = tbody.lastElementChild;
    const volInput = row.querySelector('.vol');
    const tarifInput = row.querySelector('.tarif');
    const totalInput = row.querySelector('.total');
    
    const updateTotal = () => {
        const vol = parseFloat(volInput.value) || 0;
        const tarif = parseFloat(tarifInput.value) || 0;
        totalInput.value = vol * tarif;
    };
    
    volInput.addEventListener('input', updateTotal);
    tarifInput.addEventListener('input', updateTotal);
}

function loadRincianToPeserta(kelompok, pesertaId, subIndex, rincianList) {
    const tbody = document.getElementById(`tbody-${pesertaId}`);
    if (!tbody) {
        console.error(`tbody not found for ${pesertaId}`);
        return;
    }
    
    console.log(`Loading ${rincianList.length} rincian for ${pesertaId}`);
    tbody.innerHTML = '';
    
    rincianList.forEach(rincian => {
        const index = Date.now() + Math.random();
        const hari = getJumlahHari();
        
        let jenisOptions = '<option value="">Pilih Jenis Biaya</option>';
        jenisBiayaData.forEach(j => {
            const selected = (rincian.jenis_biaya_id == j.id || rincian.jenis == j.id) ? 'selected' : '';
            jenisOptions += `<option value="${j.id}" ${selected}>${j.nama_biaya}</option>`;
        });
        
        const volume = rincian.volume || rincian.vol || hari;
        const tarif = rincian.tarif || 0;
        const total = parseFloat(volume) * parseFloat(tarif);
        
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
                        class="form-control form-control-sm"
                        value="${escapeHtml(rincian.uraian || '')}">
                </td>
                <td>
                    <input type="number" 
                        name="rincian[${kelompok}][${subIndex}][${pesertaId}][${index}][volume]" 
                        class="form-control form-control-sm vol" 
                        value="${volume}">
                </td>
                <td>
                    <input type="text" 
                        name="rincian[${kelompok}][${subIndex}][${pesertaId}][${index}][satuan]" 
                        class="form-control form-control-sm" 
                        value="${escapeHtml(rincian.satuan || 'hari')}">
                </td>
                <td>
                    <input type="number" 
                        name="rincian[${kelompok}][${subIndex}][${pesertaId}][${index}][tarif]" 
                        class="form-control form-control-sm tarif"
                        value="${tarif}">
                </td>
                <td>
                    <input type="number" 
                        name="rincian[${kelompok}][${subIndex}][${pesertaId}][${index}][total]" 
                        class="form-control form-control-sm total" 
                        value="${total}" readonly>
                </td>
                <td class="text-center">
                    <button type="button" 
                            class="btn btn-sm btn-outline-danger" 
                            onclick="this.closest('tr').remove()">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);
        
        // Setup event listeners
        const row = tbody.lastElementChild;
        const volInput = row.querySelector('.vol');
        const tarifInput = row.querySelector('.tarif');
        const totalInput = row.querySelector('.total');
        
        const updateTotal = () => {
            const v = parseFloat(volInput?.value) || 0;
            const t = parseFloat(tarifInput?.value) || 0;
            if (totalInput) totalInput.value = v * t;
        };
        
        if (volInput) volInput.addEventListener('input', updateTotal);
        if (tarifInput) tarifInput.addEventListener('input', updateTotal);
    });
}

function toggleCopyToAllButton(kelompok) {
    const container = document.getElementById(`container-${kelompok}`);
    const stCards = container.querySelectorAll('.st-card');
    
    stCards.forEach(stCard => {
        const pesertaCards = stCard.querySelectorAll('.peserta-card');
        const copyBtn = stCard.querySelector('.btn-outline-info');
        if (copyBtn) {
            copyBtn.style.display = pesertaCards.length >= 2 ? 'inline-flex' : 'none';
        }
    });
}

function copyToAllPeserta(kelompok, subIndex) {
    const stContainer = document.getElementById(`subkelompok-${kelompok}-${subIndex}`);
    const pesertaCards = stContainer.querySelectorAll('.peserta-card');

    if (pesertaCards.length < 2) {
        alert('Minimal ada 2 peserta untuk copy');
        return;
    }

    const firstPeserta = pesertaCards[0];
    const firstTbody = firstPeserta.querySelector('tbody');

    if (!firstTbody || firstTbody.querySelectorAll('tr').length === 0) {
        alert('Peserta pertama belum memiliki rincian biaya');
        return;
    }

    const firstRows = firstTbody.querySelectorAll('tr');
    let copied = 0;

    for (let i = 1; i < pesertaCards.length; i++) {
        const target = pesertaCards[i];
        const targetTbody = target.querySelector('tbody');

        if (!targetTbody) continue;

        targetTbody.innerHTML = '';

        firstRows.forEach((row) => {
            // Clone row
            const clone = row.cloneNode(true);
            
            const targetId = target.dataset.pesertaId;
            const firstId = pesertaCards[0].dataset.pesertaId;
            
            // Update name attributes dengan ID baru
            clone.querySelectorAll('select, input').forEach(field => {
                const name = field.getAttribute('name');
                if (name) {
                    const newName = name.replace(`[${firstId}]`, `[${targetId}]`);
                    field.setAttribute('name', newName);
                }
            });
            
            // PASTIKAN: Simpan nilai select sebelum diappend
            const selectElement = clone.querySelector('select');
            const selectedValue = row.querySelector('select').value;
            
            // Append ke target
            targetTbody.appendChild(clone);
            
            // SET ULANG nilai select setelah diappend
            if (selectElement && selectedValue) {
                selectElement.value = selectedValue;
            }
            
            // Re-attach event listeners
            const newRow = targetTbody.lastElementChild;
            const volInput = newRow.querySelector('.vol');
            const tarifInput = newRow.querySelector('.tarif');
            const totalInput = newRow.querySelector('.total');
            
            const updateTotal = () => {
                const vol = parseFloat(volInput?.value) || 0;
                const tarif = parseFloat(tarifInput?.value) || 0;
                if (totalInput) totalInput.value = vol * tarif;
            };
            
            if (volInput) {
                volInput.removeEventListener('input', updateTotal);
                volInput.addEventListener('input', updateTotal);
            }
            if (tarifInput) {
                tarifInput.removeEventListener('input', updateTotal);
                tarifInput.addEventListener('input', updateTotal);
            }
            
            // Hitung ulang total
            if (totalInput) {
                const vol = parseFloat(volInput?.value) || 0;
                const tarif = parseFloat(tarifInput?.value) || 0;
                totalInput.value = vol * tarif;
            }
        });
        
        copied++;
    }

    alert(`Berhasil menyalin rincian ke ${copied} peserta lainnya`);
    
    // Trigger update review
    if (typeof updateReview === 'function') {
        const step5 = document.getElementById('step5');
        if (step5 && step5.classList.contains('active')) {
            updateReview();
        }
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Expose ke global
window.loadEditData = loadEditData;
window.addSubKelompok = addSubKelompok;
window.addPegawaiRow = addPegawaiRow;
window.addNonPegawaiRow = addNonPegawaiRow;
window.addRincian = addRincian;
window.loadRincianToPeserta = loadRincianToPeserta;

document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('shown.bs.collapse', function (e) {
        const collapseEl = e.target;
        const btn = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
        if (btn) {
            const icon = btn.querySelector('i');
            icon.classList.remove('bi-chevron-down');
            icon.classList.add('bi-chevron-up');
        }
    });

    document.addEventListener('hidden.bs.collapse', function (e) {
        const collapseEl = e.target;
        const btn = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
        if (btn) {
            const icon = btn.querySelector('i');
            icon.classList.remove('bi-chevron-up');
            icon.classList.add('bi-chevron-down');
        }
    });
});

// Fungsi untuk load data edit dengan callback
function loadEditDataWithCallback(kelompok, kelompokDataArray, onComplete) {
    console.log(`loadEditDataWithCallback called for ${kelompok}:`, kelompokDataArray);
    
    if (!kelompokDataArray || kelompokDataArray.length === 0) {
        if (onComplete) onComplete();
        return;
    }
    
    // Bersihkan container
    const container = document.getElementById(`container-${kelompok}`);
    if (container) {
        container.innerHTML = '';
    }
    
    // Reset counter
    subKelompokCounter = 1;
    
    // Hitung total anggota yang akan di-load
    let totalAnggota = 0;
    kelompokDataArray.forEach(kelompokData => {
        totalAnggota += (kelompokData.items || []).length;
    });
    
    let loadedAnggota = 0;
    
    // Fungsi untuk cek apakah semua anggota sudah load
    function checkAnggotaLoaded() {
        loadedAnggota++;
        console.log(`[${kelompok}] Loaded anggota: ${loadedAnggota}/${totalAnggota}`);
        if (loadedAnggota >= totalAnggota && onComplete) {
            onComplete();
        }
    }
    
    // Loop setiap kelompok data
    kelompokDataArray.forEach((kelompokData, idx) => {
        // Buat subkelompok tanpa data anggota dulu
        const stIndex = addSubKelompok(kelompok, {
            nomor_st: kelompokData.nomor_st || '',
            tanggal_st: kelompokData.tanggal_st || ''
        });
        
        // Ambil items (anggota)
        const items = kelompokData.items || [];
        
        if (items.length === 0 && checkAnggotaLoaded) {
            // Tidak ada anggota, tetap panggil callback
            for (let i = 0; i < items.length; i++) {
                checkAnggotaLoaded();
            }
        }
        
        // Tambahkan anggota satu per satu dengan delay
        items.forEach((anggota, anggotaIdx) => {
            setTimeout(() => {
                if (anggota.type === 'pegawai' || anggota.pegawai_id) {
                    // Tambah pegawai
                    const pesertaId = addPegawaiRow(kelompok, stIndex);
                    
                    // Isi data pegawai
                    const subKelompokDiv = document.getElementById(`subkelompok-${kelompok}-${stIndex}`);
                    const pesertaCard = subKelompokDiv.querySelector(`.peserta-card[data-peserta-id="${pesertaId}"]`);
                    
                    if (pesertaCard) {
                        const select = pesertaCard.querySelector('select');
                        if (select && (anggota.pegawai_id || anggota.id)) {
                            select.value = anggota.pegawai_id || anggota.id;
                        }
                        
                        // Load rincian
                        if (anggota.rincian && anggota.rincian.length > 0) {
                            loadRincianToPeserta(kelompok, pesertaId, stIndex, anggota.rincian);
                        }
                    }
                } else {
                    // Tambah non pegawai
                    const pesertaId = addNonPegawaiRow(kelompok, stIndex);
                    
                    // Isi data non pegawai
                    const subKelompokDiv = document.getElementById(`subkelompok-${kelompok}-${stIndex}`);
                    const pesertaCard = subKelompokDiv.querySelector(`.peserta-card[data-peserta-id="${pesertaId}"]`);
                    
                    if (pesertaCard) {
                        const namaInput = pesertaCard.querySelector('input[name*="[nama]"]');
                        const nikInput = pesertaCard.querySelector('input[name*="[nik]"]');
                        const instansiInput = pesertaCard.querySelector('input[name*="[instansi]"]');
                        
                        if (namaInput) namaInput.value = anggota.nama || '';
                        if (nikInput) nikInput.value = anggota.nik || '';
                        if (instansiInput) instansiInput.value = anggota.instansi || '';
                        
                        // Load rincian
                        if (anggota.rincian && anggota.rincian.length > 0) {
                            loadRincianToPeserta(kelompok, pesertaId, stIndex, anggota.rincian);
                        }
                    }
                }
                
                // Panggil callback setelah anggota selesai di-load
                if (checkAnggotaLoaded) {
                    checkAnggotaLoaded();
                }
            }, anggotaIdx * 200);
        });
    });
}

// Expose fungsi baru ke global
window.loadEditDataWithCallback = loadEditDataWithCallback;
</script>

<style>
.table-responsive { overflow-x: auto; }
.table-responsive table { min-width: 950px; width: max-content; }
.table th, .table td { white-space: nowrap; }
.table th:nth-child(2), .table td:nth-child(2) { min-width: 280px; }
.st-card {
    transition: all 0.3s ease;
}
.st-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
</style>