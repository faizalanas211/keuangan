{{-- resources/views/dashboard/perjadin/partials/step_review.blade.php --}}

<div class="card card-shadow">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">📋 Review Data Perjalanan Dinas</h5>
    </div>
    <div class="card-body">
        
        {{-- Informasi Perjalanan --}}
        <div class="mb-4">
            <h6 class="fw-bold text-success border-bottom pb-2">A. Informasi Perjalanan</h6>
            <div class="row mt-2">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="35%"><strong>Alat Angkutan</strong></td>
                            <td id="review-alat-angkutan">-</td>
                        </tr>
                        <tr>
                            <td><strong>Dari Kota</strong></td>
                            <td id="review-dari-kota">-</td>
                        </tr>
                        <tr>
                            <td><strong>Tujuan Kota</strong></td>
                            <td id="review-tujuan-kota">-</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="35%"><strong>Tanggal Mulai</strong></td>
                            <td id="review-tanggal-mulai">-</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Akhir</strong></td>
                            <td id="review-tanggal-akhir">-</td>
                        </tr>
                        <tr>
                            <td><strong>Jumlah Hari</strong></td>
                            <td id="review-jumlah-hari">-</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <strong>Nama Kegiatan:</strong>
                    <p id="review-nama-kegiatan" class="mt-1 p-2 bg-light rounded">-</p>
                </div>
            </div>
        </div>

        {{-- Panitia --}}
        <div class="mb-4">
            <h6 class="fw-bold text-success border-bottom pb-2">B. Panitia</h6>
            <div id="review-panitia" class="mt-2">
                <p class="text-muted">Belum ada data panitia</p>
            </div>
        </div>

        {{-- Peserta --}}
        <div class="mb-4">
            <h6 class="fw-bold text-success border-bottom pb-2">C. Peserta</h6>
            <div id="review-peserta" class="mt-2">
                <p class="text-muted">Belum ada data peserta</p>
            </div>
        </div>

        {{-- Narasumber --}}
        <div class="mb-4">
            <h6 class="fw-bold text-success border-bottom pb-2">D. Narasumber</h6>
            <div id="review-narasumber" class="mt-2">
                <p class="text-muted">Belum ada data narasumber</p>
            </div>
        </div>

        {{-- Ringkasan Biaya --}}
        <div class="alert alert-info">
            <h6 class="fw-bold mb-2">💰 Ringkasan Total Biaya</h6>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="50%">Total Panitia:</td>
                            <td class="fw-bold" id="total-panitia">Rp 0</td>
                        </tr>
                        <tr>
                            <td>Total Peserta:</td>
                            <td class="fw-bold" id="total-peserta">Rp 0</td>
                        </tr>
                        <tr>
                            <td>Total Narasumber:</td>
                            <td class="fw-bold" id="total-narasumber">Rp 0</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr class="border-top">
                            <td width="50%"><strong>GRAND TOTAL</strong></td>
                            <td class="fw-bold text-success h5" id="grand-total">Rp 0</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Function to collect all form data and display in review
function updateReview() {
    // Ambil data dari step 1
    document.getElementById('review-alat-angkutan').innerText = 
        document.querySelector('[name="alat_angkutan"]')?.value || '-';
    document.getElementById('review-dari-kota').innerText = 
        document.querySelector('[name="dari_kota"]')?.value || '-';
    document.getElementById('review-tujuan-kota').innerText = 
        document.querySelector('[name="tujuan_kota"]')?.value || '-';
    document.getElementById('review-tanggal-mulai').innerText = 
        document.querySelector('[name="tanggal_mulai"]')?.value || '-';
    document.getElementById('review-tanggal-akhir').innerText = 
        document.querySelector('[name="tanggal_akhir"]')?.value || '-';
    document.getElementById('review-nama-kegiatan').innerText = 
        document.querySelector('[name="nama_kegiatan"]')?.value || '-';
    
    // Hitung jumlah hari
    const mulai = document.querySelector('[name="tanggal_mulai"]')?.value;
    const akhir = document.querySelector('[name="tanggal_akhir"]')?.value;
    if (mulai && akhir) {
        const days = (new Date(akhir) - new Date(mulai)) / (1000 * 60 * 60 * 24) + 1;
        document.getElementById('review-jumlah-hari').innerText = days;
    }
    
    // Review Panitia
    reviewTipe('panitia');
    
    // Review Peserta
    reviewTipe('peserta');
    
    // Review Narasumber
    reviewTipe('narasumber');
}

function reviewTipe(tipe) {
    const container = document.getElementById(`review-${tipe}`);
    const stCards = document.querySelectorAll(`#container-${tipe} .st-card`);

    if (!stCards.length) {
        container.innerHTML = '<p class="text-muted">Belum ada data</p>';
        document.getElementById(`total-${tipe}`).innerText = 'Rp 0';
        updateGrandTotal();
        return;
    }

    let html = '';
    let totalTipe = 0;

    stCards.forEach((stCard, idx) => {
        const nomorSt = stCard.querySelector('[name*="[nomor_st]"]')?.value || '-';
        const tanggalSt = stCard.querySelector('[name*="[tanggal_st]"]')?.value || '-';

        html += `
            <div class="card border mb-3">
                <div class="card-header bg-light">
                    <strong>ST ${idx + 1}: ${nomorSt}</strong>
                    <span class="badge bg-secondary ms-2">${tanggalSt}</span>
                </div>
                <div class="card-body p-2">
        `;

        const pesertaCards = stCard.querySelectorAll('.peserta-card');

        if (!pesertaCards.length) {
            html += '<p class="text-muted small mb-0">Belum ada peserta</p>';
        } else {
            html += `
                <table class="table table-sm table-borderless mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Identitas</th>
                            <th>Jumlah Rincian</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            let totalST = 0;

            pesertaCards.forEach(p => {
                let nama = '-';
                let identitas = '-';

                if (p.dataset.type === 'pegawai') {
                    const select = p.querySelector('select');
                    const selected = select?.options[select.selectedIndex];

                    nama = selected?.text?.split(' (')[0] || '-';
                    identitas = selected?.value || '-';
                } else {
                    nama = p.querySelector('[name*="[nama]"]')?.value || '-';
                    identitas = p.querySelector('[name*="[nik]"]')?.value || '-';
                }

                const rows = p.querySelectorAll('tbody tr');

                let totalPeserta = 0;

                rows.forEach(r => {
                    const total = parseFloat(r.querySelector('.total')?.value) || 0;
                    totalPeserta += total;
                });

                totalST += totalPeserta;

                html += `
                    <tr>
                        <td>${nama}</td>
                        <td>${identitas}</td>
                        <td>${rows.length} item</td>
                        <td class="fw-bold">${formatRupiah(totalPeserta)}</td>
                    </tr>
                `;
            });

            totalTipe += totalST;

            html += `
                    </tbody>
                </table>

                <div class="text-end mt-2 pt-2 border-top">
                    <strong>Total ST: ${formatRupiah(totalST)}</strong>
                </div>
            `;
        }

        html += `</div></div>`;
    });

    container.innerHTML = html;
    document.getElementById(`total-${tipe}`).innerText = formatRupiah(totalTipe);

    updateGrandTotal();
}

function updateGrandTotal() {
    const totalPanitia = parseRupiah(document.getElementById('total-panitia')?.innerText || 'Rp 0');
    const totalPeserta = parseRupiah(document.getElementById('total-peserta')?.innerText || 'Rp 0');
    const totalNarasumber = parseRupiah(document.getElementById('total-narasumber')?.innerText || 'Rp 0');
    
    const grandTotal = totalPanitia + totalPeserta + totalNarasumber;
    document.getElementById('grand-total').innerText = formatRupiah(grandTotal);
}

function formatRupiah(angka) {
    if (!angka || isNaN(angka)) return 'Rp 0';
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(angka));
}

function parseRupiah(string) {
    return parseInt(string.replace(/[^0-9,-]/g, '').replace(',', '')) || 0;
}

// Panggil updateReview saat step 5 di-load
document.addEventListener('DOMContentLoaded', function() {
    // Override fungsi loadStepData untuk step 5
    const originalLoadStepData = window.loadStepData;
    if (originalLoadStepData) {
        window.loadStepData = function() {
            if (currentStep === 5) {
                updateReview();
            } else if (originalLoadStepData) {
                originalLoadStepData();
            }
        };
    }
    
    // Juga update saat ada perubahan data
    setInterval(() => {
        if (currentStep === 5) {
            updateReview();
        }
    }, 500);
});
</script>