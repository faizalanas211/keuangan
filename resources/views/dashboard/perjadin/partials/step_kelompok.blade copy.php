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

// function addSubKelompok(kelompok) {

//     const container = document.getElementById(`container-${kelompok}`);

//     const index = subKelompokCounter++;

//     container.insertAdjacentHTML('beforeend', `
//         <div class="card border mb-3">
//             <div class="card-body">

//                 <div class="row mb-3">
//                     <div class="col-md-5">
//                         <label>Nomor ST</label>
//                         <input type="text" 
//                             name="kelompok[${kelompok}][${index}][nomor_st]" 
//                             class="form-control">
//                     </div>

//                     <div class="col-md-5">
//                         <label>Tanggal ST</label>
//                         <input type="date" 
//                             name="kelompok[${kelompok}][${index}][tanggal_st]" 
//                             class="form-control">
//                     </div>

//                     <div class="col-md-2 d-flex align-items-end">
//                         <button type="button" 
//                             class="btn btn-outline-danger w-100"
//                             onclick="this.closest('.card').remove()">
//                             Hapus
//                         </button>
//                     </div>
//                 </div>

//                 <div id="subkelompok-${kelompok}-${index}"></div>

//                 <button type="button" 
//                     class="btn btn-sm btn-outline-success mt-2"
//                     onclick="addPegawaiRow('${kelompok}', ${index})">
//                     + Pegawai
//                 </button>

//                 <button type="button" 
//                     class="btn btn-sm btn-outline-success mt-2"
//                     onclick="addNonPegawaiRow('${kelompok}', ${index})">
//                     + Non Pegawai
//                 </button>

//             </div>
//         </div>
//     `);
// }

function addPegawaiRow(kelompok, subIndex, customId = null) {
    const pesertaId = customId ?? `${kelompok}_${subIndex}_${Date.now()}`;
    const container = document.getElementById(`subkelompok-${kelompok}-${subIndex}`);

    let options = '<option value="">Pilih Pegawai</option>';
    pegawaiData.forEach(p => {
        options += `<option value="${p.id}">${p.nama} (${p.nip})</option>`;
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
                <select name="peserta[${kelompok}][${subIndex}][${pesertaId}][pegawai_id]" class="form-select mb-3">
                    ${options}
                </select>
                ${renderTable(kelompok, pesertaId, subIndex)}
            </div>
        </div>
    `);

    toggleCopyToAllButton(kelompok);
    return pesertaId; 
}

function addNonPegawaiRow(kelompok, subIndex, customId = null) {
    const pesertaId = customId ?? `${kelompok}_${subIndex}_${Date.now()}`;
    const container = document.getElementById(`subkelompok-${kelompok}-${subIndex}`);

    // const container = document.getElementById(`container-${kelompok}`);
    // const pesertaId = `${kelompok}_${Date.now()}_${pesertaCounter++}`;

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
            <td class="text-center">
                <button type="button" 
                        class="btn btn-sm btn-outline-danger" 
                        onclick="this.closest('tr').remove()">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `);
}

function toggleCopyToAllButton(kelompok) {
    const container = document.getElementById(`container-${kelompok}`);
    const pesertaCards = container.querySelectorAll('.peserta-card');
    const btn = document.getElementById(`btnCopy-${kelompok}`);

    if (btn) {
        btn.style.display = pesertaCards.length >= 2 ? 'inline-flex' : 'none';
    }
}

function copyToAllPeserta(kelompok, subIndex) {

    const stContainer = document.getElementById(`subkelompok-${kelompok}-${subIndex}`);
    const pesertaCards = stContainer.querySelectorAll('.peserta-card');

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

            cloneFields.forEach((field, indexField) => {

                // 🔥 penting: update name biar sesuai pesertaId target
                const sourceId = pesertaCards[0].dataset.pesertaId;
                const targetId = target.dataset.pesertaId;

                const newName = name.replace(
                    new RegExp(`\\[${sourceId}\\]`),
                    `[${targetId}]`
                );

                // copy value
                field.value = originalFields[indexField].value;
            });

            targetTbody.appendChild(clone);
        });

        copied++;
    }

    alert(`Berhasil copy ke ${copied} peserta di ST ini`);
}

function addSubKelompok(kelompok, customIndex = null) {
    const container = document.getElementById(`container-${kelompok}`);
    const index = customIndex !== null ? customIndex : subKelompokCounter++;

    container.insertAdjacentHTML('beforeend', `
        <div class="card border mb-3 st-card">
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
                        onclick="this.closest('.card').remove()">
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
                                class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Tanggal ST</label>
                            <input type="date"
                                name="kelompok[${kelompok}][${index}][tanggal_st]"
                                class="form-control">
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


</script>

<style>
.table-responsive { overflow-x: auto; }
.table-responsive table { min-width: 950px; width: max-content; }
.table th, .table td { white-space: nowrap; }
.table th:nth-child(2), .table td:nth-child(2) { min-width: 280px; }
</style>
