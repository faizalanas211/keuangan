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

<form id="multiStepForm" action="{{ route('perjadin.update', $perjalanan->id) }}" method="POST">
@csrf
@method('PUT')

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
    <!-- <div class="step-item" data-step="5">
        <div class="step-circle">5</div>
        <div class="step-label">Review & Submit</div>
    </div> -->
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
        'is_pegawai' => true,
        'kelompokData' => []
    ])
</div>

{{-- STEP 3: Peserta --}}
<div class="step" id="step3">
    @include('dashboard.perjadin.partials.step_kelompok', [
        'tipe' => 'peserta',
        'title' => 'Peserta Perjalanan Dinas',
        'is_pegawai' => true,
        'kelompokData' => []
    ])
</div>

{{-- STEP 4: Narasumber --}}
<div class="step" id="step4">
    @include('dashboard.perjadin.partials.step_kelompok', [
        'tipe' => 'narasumber',
        'title' => 'Narasumber',
        'is_pegawai' => false,
        'kelompokData' => []
    ])
</div>

{{-- STEP 5: Review --}}
<!-- <div class="step" id="step5">
    @include('dashboard.perjadin.partials.step_review')
</div> -->

{{-- Navigation Buttons --}}
<div class="d-flex justify-content-between mt-4">
    <button type="button" class="btn btn-secondary" id="prevBtn" style="display:none;">← Sebelumnya</button>
    <button type="button" class="btn btn-success" id="nextBtn">Selanjutnya →</button>
    <button type="submit" class="btn btn-success" id="submitBtn" style="display:none;">Simpan Perubahan</button>
</div>

</form>

<script>
// Data awal dari controller - sudah dalam format yang benar
const initialData = @json($initialData);
const isEdit = true;

// Global state
const formState = {
    step1: {},
    panitia: [],
    peserta: [],
    narasumber: []
};

let currentStep = 1;
const totalSteps = 4;

// Load data saat halaman siap
window.addEventListener('load', () => {
    localStorage.removeItem('perjadin_form');
    
    // Beri sedikit delay untuk memastikan DOM ready
    setTimeout(() => {
        loadInitialData();
    }, 100);
});

// Fungsi untuk load data edit
// Di dalam edit.blade.php, update fungsi loadInitialData
function loadInitialData() {
    console.log('Loading initial data...');
    
    // Load step 1 data
    renderStep1(initialData.step1);
    
    // Reset semua container terlebih dahulu
    ['panitia', 'peserta', 'narasumber'].forEach(tipe => {
        const container = document.getElementById(`container-${tipe}`);
        if (container) container.innerHTML = '';
    });
    
    // Reset counter
    if (typeof subKelompokCounter !== 'undefined') {
        subKelompokCounter = 1;
    }
    
    // Hitung berapa banyak kelompok yang akan di-load
    let totalKelompokToLoad = 0;
    let loadedKelompok = 0;
    
    if (initialData.panitia && initialData.panitia.length > 0) totalKelompokToLoad++;
    if (initialData.peserta && initialData.peserta.length > 0) totalKelompokToLoad++;
    if (initialData.narasumber && initialData.narasumber.length > 0) totalKelompokToLoad++;
    
    // Fungsi untuk cek apakah semua data sudah load
    function checkAllDataLoaded() {
        loadedKelompok++;
        console.log(`Data loaded: ${loadedKelompok}/${totalKelompokToLoad}`);
        if (loadedKelompok >= totalKelompokToLoad) {
            // Semua data sudah load, simpan ke storage dan update review jika perlu
            setTimeout(() => {
                saveToStorage();
                // Jika sedang di step 5, update review
                // if (currentStep === 5 && typeof updateReview === 'function') {
                //     setTimeout(() => {
                //         updateReview();
                //     }, 500);
                // }
            }, 500);
        }
    }
    
    // Load kelompok data dengan callback
    if (initialData.panitia && initialData.panitia.length > 0) {
        console.log('Loading panitia data...');
        if (typeof window.loadEditData === 'function') {
            // Modifikasi loadEditData untuk menerima callback
            window.loadEditDataWithCallback('panitia', initialData.panitia, checkAllDataLoaded);
        } else {
            checkAllDataLoaded();
        }
    } else {
        checkAllDataLoaded();
    }
    
    if (initialData.peserta && initialData.peserta.length > 0) {
        setTimeout(() => {
            console.log('Loading peserta data...');
            if (typeof window.loadEditDataWithCallback === 'function') {
                window.loadEditDataWithCallback('peserta', initialData.peserta, checkAllDataLoaded);
            } else {
                checkAllDataLoaded();
            }
        }, 300);
    } else {
        setTimeout(checkAllDataLoaded, 300);
    }
    
    if (initialData.narasumber && initialData.narasumber.length > 0) {
        setTimeout(() => {
            console.log('Loading narasumber data...');
            if (typeof window.loadEditDataWithCallback === 'function') {
                window.loadEditDataWithCallback('narasumber', initialData.narasumber, checkAllDataLoaded);
            } else {
                checkAllDataLoaded();
            }
        }, 600);
    } else {
        setTimeout(checkAllDataLoaded, 600);
    }
}

// Collect data dari step 1
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

// Collect data dari kelompok
function collectKelompokData(tipe) {
    const container = document.getElementById(`container-${tipe}`);
    if (!container) return [];
    
    const result = [];
    const stCards = container.querySelectorAll('.st-card');
    
    stCards.forEach(stCard => {
        const nomor = stCard.querySelector('[name*="[nomor_st]"]')?.value;
        const tanggal = stCard.querySelector('[name*="[tanggal_st]"]')?.value;
        const items = [];
        
        const pesertaCards = stCard.querySelectorAll('.peserta-card');
        pesertaCards.forEach(p => {
            let data = {
                type: p.dataset.type, 
                rincian: []
            };
            
            if (data.type === 'pegawai') {
                const select = p.querySelector('select');
                data.pegawai_id = select?.value || '';
                if (select?.selectedOptions[0]) {
                    data.nama = select.selectedOptions[0].getAttribute('data-nama');
                    data.nip = select.selectedOptions[0].getAttribute('data-nip');
                }
            } else {
                data.nama = p.querySelector('[name*="[nama]"]')?.value || '';
                data.nik = p.querySelector('[name*="[nik]"]')?.value || '';
                data.instansi = p.querySelector('[name*="[instansi]"]')?.value || '';
            }
            
            // Kumpulkan rincian
            const tbody = p.querySelector('tbody');
            if (tbody) {
                tbody.querySelectorAll('tr').forEach(row => {
                    data.rincian.push({
                        jenis_biaya_id: row.querySelector('select')?.value || '',
                        uraian: row.querySelector('[name*="[uraian]"]')?.value || '',
                        volume: row.querySelector('.vol')?.value || 0,
                        satuan: row.querySelector('[name*="[satuan]"]')?.value || '',
                        tarif: row.querySelector('.tarif')?.value || 0,
                        total: row.querySelector('.total')?.value || 0
                    });
                });
            }
            
            items.push(data);
        });
        
        result.push({
            nomor_st: nomor,
            tanggal_st: tanggal,
            items: items
        });
    });
    
    return result;
}

// Render step 1
function renderStep1(data) {
    if (!data) return;
    
    const fields = ['tingkat_perjalanan', 'alat_angkutan', 'dari_kota', 'tujuan_kota', 'kode_mak', 'akun_biaya', 'nama_kegiatan'];
    fields.forEach(field => {
        const el = document.querySelector(`[name="${field}"]`);
        if (el && data[field]) el.value = data[field];
    });
    
    const dates = ['tanggal_mulai', 'tanggal_akhir', 'tanggal_terima'];
    dates.forEach(date => {
        const el = document.getElementById(date);
        if (el && data[date]) el.value = data[date];
    });
}

// Save ke localStorage
function saveToStorage() {
    formState.step1 = collectStep1();
    formState.panitia = collectKelompokData('panitia');
    formState.peserta = collectKelompokData('peserta');
    formState.narasumber = collectKelompokData('narasumber');
    
    localStorage.setItem('perjadin_form', JSON.stringify(formState));
}

// Auto-save dengan debounce
function debounce(func, delay) {
    let timeout;
    return function() {
        clearTimeout(timeout);
        timeout = setTimeout(func, delay);
    };
}

const autoSave = debounce(saveToStorage, 500);
document.addEventListener('input', autoSave);

// Auto-calculate total
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('vol') || e.target.classList.contains('tarif')) {
        const row = e.target.closest('tr');
        if (row) {
            const vol = parseFloat(row.querySelector('.vol')?.value) || 0;
            const tarif = parseFloat(row.querySelector('.tarif')?.value) || 0;
            const total = vol * tarif;
            const totalInput = row.querySelector('.total');
            if (totalInput) totalInput.value = total;
        }
    }
});

// Navigasi steps
document.getElementById('nextBtn').addEventListener('click', nextStep);
document.getElementById('prevBtn').addEventListener('click', prevStep);

function nextStep() {
    if (!validateStep(currentStep)) return;
    
    saveToStorage();
    
    document.getElementById(`step${currentStep}`).classList.remove('active');
    currentStep++;
    document.getElementById(`step${currentStep}`).classList.add('active');
    
    // // Update review saat masuk ke step 5
    // if (currentStep === 5) {
    //     // Coba panggil berulang kali dengan interval
    //     let attempts = 0;
    //     const interval = setInterval(() => {
    //         attempts++;
    //         console.log(`Update review attempt ${attempts}`);
            
    //         if (typeof updateReview === 'function') {
    //             updateReview();
                
    //             // Cek apakah data sudah muncul
    //             const reviewPanitia = document.getElementById('review-panitia');
    //             if (reviewPanitia && reviewPanitia.innerHTML !== '<p class="text-muted">Belum ada data</p>') {
    //                 console.log('Review data appears!');
    //                 clearInterval(interval);
    //             }
    //         }
            
    //         if (attempts >= 10) {
    //             console.log('Max attempts reached');
    //             clearInterval(interval);
    //         }
    //     }, 500);
    // }
    
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
    
    document.getElementById('prevBtn').style.display = currentStep === 1 ? 'none' : 'inline-block';
    document.getElementById('nextBtn').style.display = currentStep === totalSteps ? 'none' : 'inline-block';
    document.getElementById('submitBtn').style.display = currentStep === totalSteps ? 'inline-block' : 'none';
}

function validateStep(step) {
    if (step === 1) {
        const mulai = document.getElementById('tanggal_mulai')?.value;
        const akhir = document.getElementById('tanggal_akhir')?.value;
        if (!mulai || !akhir) {
            alert('Lengkapi tanggal mulai dan akhir');
            return false;
        }
        if (new Date(mulai) > new Date(akhir)) {
            alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
            return false;
        }
        return true;
    }
    return true;
}

function updateReview() {
    const reviewContainer = document.getElementById('review-content');
    if (!reviewContainer) return;
    
    const data = {
        step1: collectStep1(),
        panitia: collectKelompokData('panitia'),
        peserta: collectKelompokData('peserta'),
        narasumber: collectKelompokData('narasumber')
    };
    
    const totalPanitia = data.panitia.reduce((sum, k) => sum + k.items.length, 0);
    const totalPeserta = data.peserta.reduce((sum, k) => sum + k.items.length, 0);
    const totalNarasumber = data.narasumber.reduce((sum, k) => sum + k.items.length, 0);
    
    reviewContainer.innerHTML = `
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Review Data Perjalanan Dinas</h5>
            </div>
            <div class="card-body">
                <h6>Informasi Perjalanan:</h6>
                <table class="table table-sm table-bordered">
                    <tr><th style="width:30%">Nama Kegiatan</th><td>${data.step1.nama_kegiatan || '-'}</td></tr>
                    <tr><th>Tingkat Perjalanan</th><td>${data.step1.tingkat_perjalanan || '-'}</td></tr>
                    <tr><th>Alat Angkutan</th><td>${data.step1.alat_angkutan || '-'}</td></tr>
                    <tr><th>Dari Kota</th><td>${data.step1.dari_kota || '-'}</td></tr>
                    <tr><th>Tujuan Kota</th><td>${data.step1.tujuan_kota || '-'}</td></tr>
                    <tr><th>Periode</th><td>${data.step1.tanggal_mulai || '-'} s/d ${data.step1.tanggal_akhir || '-'}</td></tr>
                </table>
                
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3>${data.panitia.length}</h3>
                                <p class="mb-0">Kelompok Panitia</p>
                                <small>${totalPanitia} anggota</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3>${data.peserta.length}</h3>
                                <p class="mb-0">Kelompok Peserta</p>
                                <small>${totalPeserta} anggota</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3>${data.narasumber.length}</h3>
                                <p class="mb-0">Kelompok Narasumber</p>
                                <small>${totalNarasumber} anggota</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Clear storage saat submit
document.getElementById('multiStepForm').addEventListener('submit', function() {
    localStorage.removeItem('perjadin_form');
});
</script>
@endsection