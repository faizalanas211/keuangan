@extends('layouts.admin')

@section('content')
<style>
.step {
    display: none;
}
.step.active {
    display: block;
}
.step-indicator {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    position: relative;
}
.step-indicator::before {
    content: '';
    position: absolute;
    top: 30px;
    left: 0;
    right: 0;
    height: 2px;
    background: #dee2e6;
    z-index: 1;
}
.step-item {
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 2;
}
.step-circle {
    width: 50px;
    height: 50px;
    background: #dee2e6;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    margin-bottom: 10px;
}
.step-item.active .step-circle {
    background: #16a34a;
}
.step-item.completed .step-circle {
    background: #16a34a;
}
.step-item.completed .step-circle::after {
    content: '✓';
}
.step-label {
    font-size: 14px;
    font-weight: 500;
}
</style>

<form id="multiStepForm" action="{{ route('perjadin.store') }}" method="POST">
@csrf

{{-- Step Indicator --}}
<div class="step-indicator mb-5">
    <div class="step-item active" data-step="1">
        <div class="step-circle">1</div>
        <div class="step-label">Informasi Perjalanan</div>
    </div>
    <div class="step-item" data-step="2">
        <div class="step-circle">2</div>
        <div class="step-label">Panitia</div>
    </div>
    <div class="step-item" data-step="3">
        <div class="step-circle">3</div>
        <div class="step-label">Peserta</div>
    </div>
    <div class="step-item" data-step="4">
        <div class="step-circle">4</div>
        <div class="step-label">Narasumber</div>
    </div>
    <div class="step-item" data-step="5">
        <div class="step-circle">5</div>
        <div class="step-label">Review & Submit</div>
    </div>
</div>

{{-- STEP 1: Informasi Perjalanan --}}
<div class="step active" id="step1">
    @include('dashboard.perjadin.partials.step1_informasi')
</div>

{{-- STEP 2: Panitia --}}
<div class="step" id="step2">
    @include('dashboard.perjadin.partials.step_kelompok', [
        'tipe' => 'panitia',
        'title' => 'Panitia Perjalanan Dinas',
        'is_pegawai' => true
    ])
</div>

{{-- STEP 3: Peserta --}}
<div class="step" id="step3">
    @include('dashboard.perjadin.partials.step_kelompok', [
        'tipe' => 'peserta',
        'title' => 'Peserta Perjalanan Dinas',
        'is_pegawai' => true
    ])
</div>

{{-- STEP 4: Narasumber --}}
<div class="step" id="step4">
    @include('dashboard.perjadin.partials.step_kelompok', [
        'tipe' => 'narasumber',
        'title' => 'Narasumber',
        'is_pegawai' => false
    ])
</div>

{{-- STEP 5: Review --}}
<div class="step" id="step5">
    @include('dashboard.perjadin.partials.step_review')
</div>

{{-- Navigation Buttons --}}
<div class="d-flex justify-content-between mt-4">
    <button type="button" class="btn btn-secondary" id="prevBtn" style="display:none;">← Sebelumnya</button>
    <button type="button" class="btn btn-success" id="nextBtn">Selanjutnya →</button>
    <button type="submit" class="btn btn-success" id="submitBtn" style="display:none;">Simpan Semua</button>
</div>

</form>

<script>

window.addEventListener('load', () => {
    loadFromStorage();
});

const formState = {
    step1: {},
    panitia: [],
    peserta: [],
    narasumber: []
};

function collectStep1() {
    return {
        tingkat_perjalanan: document.querySelector('[name="tingkat_perjalanan"]')?.value,
        alat_angkutan: document.querySelector('[name="alat_angkutan"]')?.value,
        dari_kota: document.querySelector('[name="dari_kota"]')?.value,
        tujuan_kota: document.querySelector('[name="tujuan_kota"]')?.value,
        tanggal_mulai: document.getElementById('tanggal_mulai')?.value,
        tanggal_akhir: document.getElementById('tanggal_akhir')?.value,
        tanggal_terima: document.getElementById('tanggal_terima')?.value,
        kode_mak: document.querySelector('[name="kode_mak"]')?.value,
        akun_biaya: document.querySelector('[name="akun_biaya"]')?.value,
        nama_kegiatan: document.querySelector('[name="nama_kegiatan"]')?.value,
    };
}

function collectKelompokData(tipe) {
    const container = document.getElementById(`container-${tipe}`);
    const result = [];

    container.querySelectorAll('.st-card').forEach(stCard => {
        const nomor = stCard.querySelector('[name*="[nomor_st]"]')?.value;
        const tanggal = stCard.querySelector('[name*="[tanggal_st]"]')?.value;

        const items = [];

        stCard.querySelectorAll('.peserta-card').forEach(p => {
            let data = {
                type: p.dataset.type, 
                rincian: []
            };

            if (data.type === 'pegawai') {
                data.pegawai_id = p.querySelector('select').value;
            } else {
                data.nama = p.querySelector('[name*="[nama]"]').value;
                data.nik = p.querySelector('[name*="[nik]"]').value;
                data.instansi = p.querySelector('[name*="[instansi]"]').value;
            }

            p.querySelectorAll('tbody tr').forEach(row => {
                data.rincian.push({
                    jenis: row.querySelector('select')?.value,
                    uraian: row.querySelector('[name*="[uraian]"]')?.value,
                    volume: row.querySelector('.vol')?.value,
                    tarif: row.querySelector('.tarif')?.value
                });
            });

            items.push(data);
        });

        result.push({
            nomor_st: nomor,
            tanggal_st: tanggal,
            items
        });
    });

    return result;
}

function renderStep1(data) {
    if (!data) return;

    document.querySelector('[name="tingkat_perjalanan"]').value = data.tingkat_perjalanan || '';
    document.querySelector('[name="alat_angkutan"]').value = data.alat_angkutan || '';
    document.querySelector('[name="dari_kota"]').value = data.dari_kota || '';
    document.querySelector('[name="tujuan_kota"]').value = data.tujuan_kota || '';
    document.getElementById('tanggal_mulai').value = data.tanggal_mulai || '';
    document.getElementById('tanggal_akhir').value = data.tanggal_akhir || '';
    document.getElementById('tanggal_terima').value = data.tanggal_terima || '';
    document.querySelector('[name="kode_mak"]').value = data.kode_mak || '';
    document.querySelector('[name="akun_biaya"]').value = data.akun_biaya || '';
    document.querySelector('[name="nama_kegiatan"]').value = data.nama_kegiatan || '';
}

function renderKelompok(tipe, data) {
    const container = document.getElementById(`container-${tipe}`);
    container.innerHTML = '';

    subKelompokCounter = 1; 

    data.forEach(st => {

        const stIndex = addSubKelompok(tipe);

        const cards = container.querySelectorAll('.st-card');
        const stCard = cards[cards.length - 1];

        stCard.querySelector('[name*="[nomor_st]"]').value = st.nomor_st || '';
        stCard.querySelector('[name*="[tanggal_st]"]').value = st.tanggal_st || '';

        st.items.forEach(item => {

            let pesertaId;

            if (item.type === 'pegawai') {
                pesertaId = addPegawaiRow(tipe, stIndex);

                const pesertaCard = stCard.querySelector(`[data-peserta-id="${pesertaId}"]`);
                pesertaCard.querySelector('select').value = item.pegawai_id || '';

            } else {
                pesertaId = addNonPegawaiRow(tipe, stIndex);

                const pesertaCard = stCard.querySelector(`[data-peserta-id="${pesertaId}"]`);
                pesertaCard.querySelector('[name*="[nama]"]').value = item.nama || '';
                pesertaCard.querySelector('[name*="[nik]"]').value = item.nik || '';
                pesertaCard.querySelector('[name*="[instansi]"]').value = item.instansi || '';
            }

            const tbody = stCard.querySelector(`#tbody-${pesertaId}`);

            item.rincian.forEach(r => {
                addRincian(tipe, pesertaId, stIndex);

                const lastRow = tbody.querySelector('tr:last-child');

                lastRow.querySelector('select').value = r.jenis || '';
                lastRow.querySelector('[name*="[uraian]"]').value = r.uraian || '';
                lastRow.querySelector('.vol').value = r.volume || 0;
                lastRow.querySelector('.tarif').value = r.tarif || 0;

                const v = parseFloat(r.volume) || 0;
                const t = parseFloat(r.tarif) || 0;
                lastRow.querySelector('.total').value = v * t;
            });

        });

    });
}

function saveToStorage() {
    formState.step1 = collectStep1();
    formState.panitia = collectKelompokData('panitia');
    formState.peserta = collectKelompokData('peserta');
    formState.narasumber = collectKelompokData('narasumber');

    localStorage.setItem('perjadin_form', JSON.stringify(formState));
}

function loadFromStorage() {
    const saved = JSON.parse(localStorage.getItem('perjadin_form'));
    if (!saved) return;

    formState.step1 = saved.step1 || {};
    formState.panitia = saved.panitia || [];
    formState.peserta = saved.peserta || [];
    formState.narasumber = saved.narasumber || [];

    renderStep1(formState.step1); 

    renderKelompok('panitia', formState.panitia);
    renderKelompok('peserta', formState.peserta);
    renderKelompok('narasumber', formState.narasumber);
}

document.addEventListener('input', debounce(saveToStorage, 500));

function debounce(func, delay) {
    let timeout;
    return function () {
        clearTimeout(timeout);
        timeout = setTimeout(func, delay);
    };
}

document.addEventListener('input', function(e){
    if (e.target.classList.contains('vol') || e.target.classList.contains('tarif')) {
        const row = e.target.closest('tr');
        const v = parseFloat(row.querySelector('.vol').value) || 0;
        const t = parseFloat(row.querySelector('.tarif').value) || 0;
        row.querySelector('.total').value = v * t;
    }
});

let currentStep = 1;
const totalSteps = 5;

// Navigasi
document.getElementById('nextBtn').addEventListener('click', nextStep);
document.getElementById('prevBtn').addEventListener('click', prevStep);

function nextStep() {
    if (!validateStep(currentStep)) return;

    saveToStorage();

    document.getElementById(`step${currentStep}`).classList.remove('active');
    currentStep++;
    document.getElementById(`step${currentStep}`).classList.add('active');

    if (currentStep === 5) {
        updateReview(); 
    }

    updateStepIndicator();
}

function prevStep() {
    if (currentStep > 1) {
        document.getElementById(`step${currentStep}`).classList.remove('active');
        currentStep--;
        document.getElementById(`step${currentStep}`).classList.add('active');
        updateStepIndicator();
    }
}

function updateStepIndicator() {
    // Update active/completed states
    document.querySelectorAll('.step-item').forEach((item, index) => {
        const stepNum = index + 1;
        if (stepNum < currentStep) {
            item.classList.add('completed');
            item.classList.remove('active');
        } else if (stepNum === currentStep) {
            item.classList.add('active');
            item.classList.remove('completed');
        } else {
            item.classList.remove('active', 'completed');
        }
    });
    
    // Toggle buttons
    document.getElementById('prevBtn').style.display = currentStep === 1 ? 'none' : 'inline-block';
    document.getElementById('nextBtn').style.display = currentStep === totalSteps ? 'none' : 'inline-block';
    document.getElementById('submitBtn').style.display = currentStep === totalSteps ? 'inline-block' : 'none';
}

function validateStep(step) {

    if (step === 1) {

        const requiredFields = document.querySelectorAll(
            '#step1 [required]'
        );

        for (const field of requiredFields) {

            if (!field.value.trim()) {

                const label = field
                    .closest('.mb-3, .col-md-6, .col-md-4')
                    ?.querySelector('label')
                    ?.innerText
                    ?.replace('*', '')
                    ?.trim();

                alert(`${label || 'Field'} wajib diisi`);

                field.focus();

                return false;
            }
        }

        const mulai = document.getElementById('tanggal_mulai')?.value;
        const akhir = document.getElementById('tanggal_akhir')?.value;

        if (new Date(mulai) > new Date(akhir)) {

            alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');

            return false;
        }

        return true;
    }

    return true;
}

function getCurrentTipe() {
    if (currentStep === 2) return 'panitia';
    if (currentStep === 3) return 'peserta';
    return 'narasumber';
}

document.getElementById('multiStepForm').addEventListener('submit', function () {

    localStorage.removeItem('perjadin_form');

    sessionStorage.removeItem('perjadin_form');
});
</script>
@endsection