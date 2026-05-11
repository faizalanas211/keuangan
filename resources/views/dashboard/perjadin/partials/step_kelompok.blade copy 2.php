{{-- ================= PESERTA (TAB KELOMPOK) ================= --}}
<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-0" style="color: #2d6a4f;">
                <i class="fas fa-users me-2" style="color: #2d6a4f;"></i>{{ $title }}
            </h5>
            <p class="text-muted small mb-0 mt-1">Kelompok perjalanan dinas</p>
        </div>

        <button type="button"
            class="btn btn-sm rounded-pill px-3"
            style="background-color: #2d6a4f; border-color: #2d6a4f; color: white;"
            onclick="addSubKelompok('{{ $tipe }}')">
            <i class="fas fa-plus me-1"></i> Tambah ST
        </button>
    </div>

    <div id="container-{{ $tipe }}" class="sub-kelompok-container"></div>
</div>

<style>
    .sub-kelompok-container {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    
    .st-card-modern {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.2s ease;
        background: white;
        position: relative;
    }
    
    .st-card-modern:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .st-header-modern {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 0.875rem 1.25rem;
        border-bottom: 1px solid #e2e8f0;
        cursor: pointer;
    }
    
    .st-header-modern:hover {
        background: linear-gradient(135deg, #f1f5f9 0%, #e9ecef 100%);
    }
    
    .st-body-modern {
        padding: 1.25rem;
        background: white;
    }
    
    .peserta-card-modern {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        margin-bottom: 1rem;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    
    .peserta-card-modern:last-child {
        margin-bottom: 0;
    }
    
    .peserta-card-modern:hover {
        border-color: #cbd5e1;
    }
    
    .peserta-header {
        background: #fafbfc;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .btn-action {
        border-radius: 8px;
        padding: 0.375rem 0.875rem;
        font-size: 0.8rem;
        transition: all 0.2s;
    }
    
    .btn-action:hover {
        transform: translateY(-1px);
    }
    
    .table-rincian-modern {
        width: 100%;
        font-size: 0.85rem;
    }
    
    .table-rincian-modern th {
        background: #f8fafc;
        padding: 0.625rem;
        font-weight: 600;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .table-rincian-modern td {
        padding: 0.5rem;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .btn-delete-st {
        border-radius: 8px;
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
        transition: all 0.2s;
    }
    
    .btn-delete-st:hover {
        transform: translateY(-1px);
    }

    /* Warna kombinasi: Hijau + Biru + Abu-abu */
    .status-badge {
        background: #e8f5e9;
        color: #2d6a4f;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 500;
    }

    /* Hijau untuk elemen utama */
    .badge-success-custom {
        background-color: #2d6a4f;
        color: white;
    }

    /* Biru soft untuk aksen */
    .text-blue-soft {
        color: #3b82f6 !important;
    }
    
    .bg-blue-soft {
        background-color: #eff6ff !important;
    }

    .border-blue-soft {
        border-color: #3b82f6 !important;
    }

    .btn-outline-blue-soft {
        color: #3b82f6;
        border-color: #3b82f6;
        background: transparent;
    }

    .btn-outline-blue-soft:hover {
        background-color: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
</style>

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

// Fungsi untuk menghapus sub kelompok
function deleteSubKelompok(button, kelompok, index) {
    if (confirm('Apakah Anda yakin ingin menghapus Sub Kelompok ST ini?')) {
        const stCard = button.closest('.st-card-modern');
        if (stCard) {
            stCard.remove();
        }
    }
}

// Fungsi utama untuk menambah sub kelompok
function addSubKelompok(kelompok, existingData = null) {
    const container = document.getElementById(`container-${kelompok}`);
    const index = subKelompokCounter++;
    const collapseId = `collapse-${kelompok}-${index}`;

    container.insertAdjacentHTML('beforeend', `
        <div class="st-card-modern" data-st-index="${index}">
            <div class="st-header-modern d-flex justify-content-between align-items-center" 
                 data-bs-toggle="collapse" 
                 data-bs-target="#${collapseId}"
                 aria-expanded="true">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-ticket-alt" style="color: #2d6a4f;"></i>
                    <div>
                        <strong class="text-dark">Sub Kelompok ST</strong>
                        <small class="text-muted d-block">Klik untuk buka detail peserta</small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" 
                        class="btn btn-sm btn-outline-danger btn-delete-st"
                        onclick="deleteSubKelompok(this, '${kelompok}', ${index})">
                        <i class="fas fa-trash-alt me-1"></i> Hapus
                    </button>
                    <i class="fas fa-chevron-down text-muted toggle-icon"></i>
                </div>
            </div>

            <div class="collapse show" id="${collapseId}">
                <div class="st-body-modern">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Nomor ST</label>
                            <input type="text"
                                name="kelompok[${kelompok}][${index}][nomor_st]"
                                class="form-control form-control-sm"
                                placeholder="Masukkan nomor ST"
                                value="${existingData?.nomor_st || ''}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Tanggal ST</label>
                            <input type="date"
                                name="kelompok[${kelompok}][${index}][tanggal_st]"
                                class="form-control form-control-sm"
                                value="${existingData?.tanggal_st || ''}">
                        </div>
                    </div>

                    <div id="subkelompok-${kelompok}-${index}" class="peserta-list"></div>

                    <div class="d-flex gap-2 mt-3 pt-2 border-top">
                        <button type="button"
                            class="btn btn-sm btn-action"
                            style="background-color: #2d6a4f; border-color: #2d6a4f; color: white;"
                            onclick="addPegawaiRow('${kelompok}', ${index})">
                            <i class="fas fa-user-tie me-1"></i> + Pegawai
                        </button>
                        <button type="button"
                            class="btn btn-sm btn-action"
                            style="background-color: #3b82f6; border-color: #3b82f6; color: white;"
                            onclick="addNonPegawaiRow('${kelompok}', ${index})">
                            <i class="fas fa-user-friends me-1"></i> + Non Pegawai
                        </button>
                        <button type="button"
                            class="btn btn-sm btn-action text-dark"
                            style="background-color: #ffb703; border-color: #ffb703;"
                            onclick="copyToAllPeserta('${kelompok}', ${index})">
                            <i class="fas fa-copy me-1"></i> Salin Rincian (Pertama → Semua)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `);

    // Setup collapse icon toggle
    const header = container.lastElementChild.querySelector('.st-header-modern');
    const icon = header.querySelector('.toggle-icon');
    const collapse = new bootstrap.Collapse(document.getElementById(collapseId), { toggle: false });
    
    header.addEventListener('click', (e) => {
        if (!e.target.closest('button')) {
            collapse.toggle();
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        }
    });

    return index;
}

function addPegawaiRow(kelompok, subIndex) {
    const container = document.getElementById(`subkelompok-${kelompok}-${subIndex}`);
    const pesertaId = `${kelompok}_${subIndex}_${Date.now()}_${Math.random()}`;

    let options = '<option value="">-- Pilih Pegawai --</option>';
    pegawaiData.forEach(p => {
        options += `<option value="${p.id}" data-nama="${p.nama}" data-nip="${p.nip}">${p.nama} (${p.nip})</option>`;
    });

    container.insertAdjacentHTML('beforeend', `
        <div class="peserta-card-modern" data-peserta-id="${pesertaId}" data-type="pegawai">
            <div class="peserta-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-user-tie" style="color: #2d6a4f;"></i>
                    <strong style="color: #2d6a4f;">Pegawai</strong>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" 
                        onclick="this.closest('.peserta-card-modern').remove()">
                    <i class="fas fa-trash-alt me-1"></i> Hapus
                </button>
            </div>
            <div class="p-3">
                <select name="peserta[${kelompok}][${subIndex}][${pesertaId}][pegawai_id]" 
                        class="form-select form-select-sm mb-3">
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
        <div class="peserta-card-modern" data-peserta-id="${pesertaId}" data-type="nonpegawai">
            <div class="peserta-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-user-friends" style="color: #3b82f6;"></i>
                    <strong style="color: #3b82f6;">Non Pegawai</strong>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" 
                        onclick="this.closest('.peserta-card-modern').remove()">
                    <i class="fas fa-trash-alt me-1"></i> Hapus
                </button>
            </div>
            <div class="p-3">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                        <input type="text"
                            name="peserta[${kelompok}][${subIndex}][${pesertaId}][nama]"
                            class="form-control form-control-sm"
                            placeholder="Masukkan nama lengkap">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-secondary">NIP / NIK</label>
                        <input type="text"
                            name="peserta[${kelompok}][${subIndex}][${pesertaId}][nik]"
                            class="form-control form-control-sm"
                            placeholder="Masukkan NIP / NIK">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-secondary">Instansi / Jabatan</label>
                        <input type="text"
                            name="peserta[${kelompok}][${subIndex}][${pesertaId}][instansi]"
                            class="form-control form-control-sm"
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
                class="btn btn-sm btn-outline-blue-soft mb-3"
                onclick="addRincian('${kelompok}','${pesertaId}', ${subIndex})">
                <i class="fas fa-plus me-1"></i> Tambah Rincian
            </button>

            <div class="table-responsive">
                <table class="table table-rincian-modern">
                    <thead>
                        <tr>
                            <th style="width:15%">Jenis Biaya</th>
                            <th style="width:25%">Uraian</th>
                            <th style="width:10%">Vol</th>
                            <th style="width:10%">Satuan</th>
                            <th style="width:15%">Tarif</th>
                            <th style="width:15%">Total</th>
                            <th style="width:10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-${pesertaId}"></tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="5" class="text-end fw-semibold">Subtotal:</td>
                            <td colspan="2" class="fw-bold" style="color: #2d6a4f;" id="subtotal-${pesertaId}">Rp 0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    `;
}

function addRincian(kelompok, pesertaId, subIndex) {
    const tbody = document.getElementById(`tbody-${pesertaId}`);
    const index = Date.now() + Math.random();
    const hari = getJumlahHari();

    let jenisOptions = '<option value="">-- Pilih Jenis Biaya --</option>';
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
                    class="form-control form-control-sm"
                    placeholder="Uraian">
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
                    class="form-control form-control-sm tarif"
                    placeholder="Tarif">
            </td>
            <td>
                <input type="number" 
                    name="rincian[${kelompok}][${subIndex}][${pesertaId}][${index}][total]" 
                    class="form-control form-control-sm total bg-light" 
                    readonly>
            </td>
           <td class="text-center">
                <button type="button" 
                        class="btn btn-sm btn-outline-danger rounded-circle" 
                        onclick="this.closest('tr').remove(); updateSubtotal('${pesertaId}')">
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
        updateSubtotal(pesertaId);
    };
    
    volInput.addEventListener('input', updateTotal);
    tarifInput.addEventListener('input', updateTotal);
    updateSubtotal(pesertaId);
}

function updateSubtotal(pesertaId) {
    const tbody = document.getElementById(`tbody-${pesertaId}`);
    if (!tbody) return;
    
    let subtotal = 0;
    tbody.querySelectorAll('.total').forEach(totalInput => {
        subtotal += parseFloat(totalInput.value) || 0;
    });
    
    const subtotalSpan = document.getElementById(`subtotal-${pesertaId}`);
    if (subtotalSpan) {
        subtotalSpan.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
    }
}

function loadRincianToPeserta(kelompok, pesertaId, subIndex, rincianList) {
    const tbody = document.getElementById(`tbody-${pesertaId}`);
    if (!tbody) {
        console.error(`tbody not found for ${pesertaId}`);
        return;
    }
    
    tbody.innerHTML = '';
    
    rincianList.forEach(rincian => {
        const index = Date.now() + Math.random();
        const hari = getJumlahHari();
        
        let jenisOptions = '<option value="">-- Pilih Jenis Biaya --</option>';
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
                        class="form-control form-control-sm total bg-light" 
                        value="${total}" readonly>
                </td>
                <td class="text-center">
                    <button type="button" 
                            class="btn btn-sm btn-outline-danger rounded-circle" 
                            onclick="this.closest('tr').remove(); updateSubtotal('${pesertaId}')">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `);
        
        // Setup event listeners
        const row = tbody.lastElementChild;
        const volInput = row.querySelector('.vol');
        const tarifInput = row.querySelector('.tarif');
        
        const updateTotal = () => {
            const v = parseFloat(volInput?.value) || 0;
            const t = parseFloat(tarifInput?.value) || 0;
            row.querySelector('.total').value = v * t;
            updateSubtotal(pesertaId);
        };
        
        if (volInput) volInput.addEventListener('input', updateTotal);
        if (tarifInput) tarifInput.addEventListener('input', updateTotal);
    });
    
    updateSubtotal(pesertaId);
}

function toggleCopyToAllButton(kelompok) {
    const container = document.getElementById(`container-${kelompok}`);
    const stCards = container.querySelectorAll('.st-card-modern');
    
    stCards.forEach(stCard => {
        const pesertaCards = stCard.querySelectorAll('.peserta-card-modern');
        const copyBtn = stCard.querySelector('.btn-warning');
        if (copyBtn) {
            copyBtn.style.display = pesertaCards.length >= 2 ? 'inline-flex' : 'none';
        }
    });
}

function copyToAllPeserta(kelompok, subIndex) {
    const stContainer = document.getElementById(`subkelompok-${kelompok}-${subIndex}`);
    const pesertaCards = stContainer.querySelectorAll('.peserta-card-modern');

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

            const clone = row.cloneNode(true);

            // ambil value select asli
            const originalSelect = row.querySelector('select');
            const selectedValue = originalSelect ? originalSelect.value : '';

            const targetId = target.dataset.pesertaId;
            const firstId = pesertaCards[0].dataset.pesertaId;

            clone.querySelectorAll('select, input').forEach(field => {
                const name = field.getAttribute('name');

                if (name) {
                    const newName = name.replace(`[${firstId}]`, `[${targetId}]`);
                    field.setAttribute('name', newName);
                }
            });

            targetTbody.appendChild(clone);

            // restore selected option
            const clonedSelect = clone.querySelector('select');
            if (clonedSelect) {
                clonedSelect.value = selectedValue;
            }

            // Setup ulang event listeners
            const newRow = targetTbody.lastElementChild;

            const volInput = newRow.querySelector('.vol');
            const tarifInput = newRow.querySelector('.tarif');

            const updateTotal = () => {
                const v = parseFloat(volInput?.value) || 0;
                const t = parseFloat(tarifInput?.value) || 0;

                newRow.querySelector('.total').value = v * t;

                updateSubtotal(targetId);
            };

            if (volInput) {
                volInput.addEventListener('input', updateTotal);
            }

            if (tarifInput) {
                tarifInput.addEventListener('input', updateTotal);
            }
        });
        
        updateSubtotal(target.dataset.pesertaId);
        copied++;
    }

    alert(`Berhasil menyalin rincian ke ${copied} peserta lainnya`);
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
window.addSubKelompok = addSubKelompok;
window.deleteSubKelompok = deleteSubKelompok;
window.addPegawaiRow = addPegawaiRow;
window.addNonPegawaiRow = addNonPegawaiRow;
window.addRincian = addRincian;
window.loadRincianToPeserta = loadRincianToPeserta;
window.copyToAllPeserta = copyToAllPeserta;
window.updateSubtotal = updateSubtotal;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize any existing data if needed
});
</script>