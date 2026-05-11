{{-- resources/views/dashboard/perjadin/partials/step_review.blade.php --}}

<div class="card card-shadow border-0 rounded-4">
    <div class="card-header rounded-top-4" style="background: linear-gradient(135deg, #2d6a4f 0%, #40916c 100%);">
        <h5 class="mb-0 text-white">
            <i class="fas fa-clipboard-list me-2"></i> Review Data Perjalanan Dinas
        </h5>
    </div>
    <div class="card-body p-4">
        
        {{-- Informasi Perjalanan --}}
        <div class="mb-4">
            <h6 class="fw-bold border-bottom pb-2" style="color: #2d6a4f;">
                <i class="fas fa-briefcase me-2" style="color: #3b82f6;"></i>A. Informasi Perjalanan
            </h6>
            <div class="row mt-3">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="35%" class="text-secondary">Alat Angkutan</td>
                            <td class="fw-semibold" id="review-alat-angkutan">-</td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Dari Kota</td>
                            <td class="fw-semibold" id="review-dari-kota">-</td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Tujuan Kota</td>
                            <td class="fw-semibold" id="review-tujuan-kota">-</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="35%" class="text-secondary">Tanggal Mulai</td>
                            <td class="fw-semibold" id="review-tanggal-mulai">-</td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Tanggal Akhir</td>
                            <td class="fw-semibold" id="review-tanggal-akhir">-</td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Jumlah Hari</td>
                            <td class="fw-semibold" id="review-jumlah-hari">-</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="info-label" style="color: #4b5563; font-size: 0.8rem; font-weight: 600;">Nama Kegiatan</div>
                    <div class="p-3 rounded-3 mt-1" style="background: #f0fdf4;" id="review-nama-kegiatan">-</div>
                </div>
            </div>
        </div>

        {{-- Panitia --}}
        <div class="mb-4">
            <h6 class="fw-bold border-bottom pb-2" style="color: #2d6a4f;">
                <i class="fas fa-user-tie me-2" style="color: #3b82f6;"></i>B. Panitia
            </h6>
            <div id="review-panitia" class="mt-3">
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block" style="color: #9ca3af;"></i>
                    <span>Belum ada data panitia</span>
                </div>
            </div>
        </div>

        {{-- Peserta --}}
        <div class="mb-4">
            <h6 class="fw-bold border-bottom pb-2" style="color: #2d6a4f;">
                <i class="fas fa-users me-2" style="color: #3b82f6;"></i>C. Peserta
            </h6>
            <div id="review-peserta" class="mt-3">
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block" style="color: #9ca3af;"></i>
                    <span>Belum ada data peserta</span>
                </div>
            </div>
        </div>

        {{-- Narasumber --}}
        <div class="mb-4">
            <h6 class="fw-bold border-bottom pb-2" style="color: #2d6a4f;">
                <i class="fas fa-chalkboard-user me-2" style="color: #3b82f6;"></i>D. Narasumber
            </h6>
            <div id="review-narasumber" class="mt-3">
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block" style="color: #9ca3af;"></i>
                    <span>Belum ada data narasumber</span>
                </div>
            </div>
        </div>

        {{-- Ringkasan Biaya --}}
        <div class="rounded-4 mt-4" style="background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%); border: 1px solid #d1fae5;">
            <div class="p-4">
                <h6 class="fw-bold mb-3" style="color: #2d6a4f;">
                    <i class="fas fa-calculator me-2" style="color: #3b82f6;"></i>Ringkasan Total Biaya
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td width="50%" class="text-secondary">Total Panitia</td>
                                <td class="fw-bold" style="color: #2d6a4f;" id="total-panitia">Rp 0</td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Total Peserta</td>
                                <td class="fw-bold" style="color: #2d6a4f;" id="total-peserta">Rp 0</td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Total Narasumber</td>
                                <td class="fw-bold" style="color: #2d6a4f;" id="total-narasumber">Rp 0</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr class="border-top">
                                <td width="50%"><strong style="color: #1f2937;">GRAND TOTAL</strong></td>
                                <td class="fw-bold h4" style="color: #2d6a4f;" id="grand-total">Rp 0</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Function to collect all form data and display in review
function updateReview() {
    console.log('Updating review...');
    
    // Ambil data dari step 1
    document.getElementById('review-alat-angkutan').innerText = 
        document.querySelector('[name="alat_angkutan"]')?.value || '-';
    document.getElementById('review-dari-kota').innerText = 
        document.querySelector('[name="dari_kota"]')?.value || '-';
    document.getElementById('review-tujuan-kota').innerText = 
        document.querySelector('[name="tujuan_kota"]')?.value || '-';
    document.getElementById('review-tanggal-mulai').innerText = 
        document.getElementById('tanggal_mulai')?.value || '-';
    document.getElementById('review-tanggal-akhir').innerText = 
        document.getElementById('tanggal_akhir')?.value || '-';
    document.getElementById('review-nama-kegiatan').innerText = 
        document.querySelector('[name="nama_kegiatan"]')?.value || '-';
    
    // Hitung jumlah hari
    const mulai = document.getElementById('tanggal_mulai')?.value;
    const akhir = document.getElementById('tanggal_akhir')?.value;
    if (mulai && akhir) {
        const days = (new Date(akhir) - new Date(mulai)) / (1000 * 60 * 60 * 24) + 1;
        document.getElementById('review-jumlah-hari').innerText = days + ' hari';
    }
    
    // Review semua tipe
    reviewTipe('panitia');
    reviewTipe('peserta');
    reviewTipe('narasumber');
}

function reviewTipe(tipe) {
    const container = document.getElementById(`review-${tipe}`);
    // Coba cari dengan class .st-card-modern (kode baru) atau .st-card (kode lama)
    let stCards = document.querySelectorAll(`#container-${tipe} .st-card-modern`);
    if (!stCards || stCards.length === 0) {
        stCards = document.querySelectorAll(`#container-${tipe} .st-card`);
    }

    if (!container) {
        console.error(`Container review-${tipe} not found`);
        return;
    }

    if (!stCards || stCards.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="fas fa-inbox fa-2x mb-2 d-block" style="color: #9ca3af;"></i>
                <span>Belum ada data ${tipe}</span>
            </div>
        `;
        const totalElement = document.getElementById(`total-${tipe}`);
        if (totalElement) totalElement.innerText = 'Rp 0';
        updateGrandTotal();
        return;
    }

    let html = '<div class="review-list">';
    let totalTipe = 0;

    stCards.forEach((stCard, idx) => {
        const nomorSt = stCard.querySelector('[name*="[nomor_st]"]')?.value || '-';
        const tanggalSt = stCard.querySelector('[name*="[tanggal_st]"]')?.value || '-';
        
        // Format tanggal
        let formattedTanggal = tanggalSt;
        if (tanggalSt && tanggalSt !== '-') {
            const date = new Date(tanggalSt);
            formattedTanggal = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        html += `
            <div class="card border-0 mb-3 rounded-3 overflow-hidden" style="box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div class="py-3 px-4" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-left: 4px solid #2d6a4f;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <i class="fas fa-ticket-alt me-2" style="color: #3b82f6;"></i>
                            <strong style="color: #1f2937;">ST ${idx + 1}: ${escapeHtml(nomorSt)}</strong>
                            <span class="ms-2 px-2 py-1 rounded" style="background: #e8f5e9; color: #2d6a4f; font-size: 0.7rem; font-weight: 500;">${escapeHtml(formattedTanggal)}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
        `;

        // Cari peserta cards - coba beberapa selector
        let pesertaCards = stCard.querySelectorAll('.peserta-card-modern');
        if (!pesertaCards || pesertaCards.length === 0) {
            pesertaCards = stCard.querySelectorAll('.peserta-card');
        }

        if (!pesertaCards.length) {
            html += '<div class="text-center py-3 text-muted"><small>Belum ada peserta</small></div>';
        } else {
            html += `
                <div>
                    <table class="table table-sm mb-0">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th class="ps-3" style="color: #4b5563;">Nama</th>
                                <th class="text-center" style="color: #4b5563;">Rincian</th>
                                <th class="text-end pe-3" style="color: #4b5563;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            let totalST = 0;

            pesertaCards.forEach(p => {
                let nama = '-';

                if (p.dataset.type === 'pegawai') {
                    const select = p.querySelector('select');
                    const selected = select?.options[select.selectedIndex];
                    nama = selected?.text?.split(' (')[0] || '-';
                } else {
                    const namaInput = p.querySelector('[name*="[nama]"]');
                    nama = namaInput?.value || '-';
                }

                // Cari tbody dan hitung rincian
                let tbody = p.querySelector('tbody');
                if (!tbody) {
                    tbody = p.querySelector('.table tbody');
                }
                
                const rows = tbody ? tbody.querySelectorAll('tr') : [];
                let totalPeserta = 0;

                rows.forEach(r => {
                    const totalInput = r.querySelector('.total');
                    const total = parseFloat(totalInput?.value) || 0;
                    totalPeserta += total;
                });

                totalST += totalPeserta;

                const isPegawai = p.dataset.type === 'pegawai';
                html += `
                    <tr style="border-bottom: 1px solid #f0f0f0;">
                        <td class="ps-3">
                            <i class="fas ${isPegawai ? 'fa-user-tie' : 'fa-user-friends'} me-2" style="color: ${isPegawai ? '#2d6a4f' : '#3b82f6'};"></i>
                            ${escapeHtml(nama)}
                        </td>
                        <td class="text-center">
                            <span class="px-2 py-1 rounded" style="background: #eff6ff; color: #3b82f6; font-size: 0.7rem; font-weight: 500;">${rows.length} item</span>
                        </td>
                        <td class="text-end pe-3 fw-bold" style="color: #2d6a4f;">${formatRupiah(totalPeserta)}</td>
                    </tr>
                `;
            });

            totalTipe += totalST;

            html += `
                        </tbody>
                        <tfoot style="background: #f8fafc; border-top: 2px solid #e2e8f0;">
                            <tr>
                                <td colspan="2" class="text-end fw-semibold" style="color: #4b5563;">Total ST:</td>
                                <td class="text-end pe-3 fw-bold" style="color: #3b82f6;">${formatRupiah(totalST)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;
        }

        html += `</div></div>`;
    });

    html += '</div>';
    container.innerHTML = html;
    
    const totalElement = document.getElementById(`total-${tipe}`);
    if (totalElement) {
        totalElement.innerText = formatRupiah(totalTipe);
    }

    updateGrandTotal();
}

function updateGrandTotal() {
    const totalPanitia = parseRupiah(document.getElementById('total-panitia')?.innerText || 'Rp 0');
    const totalPeserta = parseRupiah(document.getElementById('total-peserta')?.innerText || 'Rp 0');
    const totalNarasumber = parseRupiah(document.getElementById('total-narasumber')?.innerText || 'Rp 0');
    
    const grandTotal = totalPanitia + totalPeserta + totalNarasumber;
    const grandTotalElement = document.getElementById('grand-total');
    if (grandTotalElement) {
        grandTotalElement.innerText = formatRupiah(grandTotal);
    }
}

function formatRupiah(angka) {
    if (!angka || isNaN(angka)) return 'Rp 0';
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(angka));
}

function parseRupiah(string) {
    if (!string) return 0;
    const parsed = parseInt(string.replace(/[^0-9,-]/g, '').replace(',', '')) || 0;
    return parsed;
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

// Fungsi untuk memaksa update review (bisa dipanggil dari tombol)
function forceUpdateReview() {
    console.log('Force update review...');
    setTimeout(() => {
        updateReview();
    }, 100);
}

// Expose ke global
window.updateReview = updateReview;
window.reviewTipe = reviewTipe;
window.forceUpdateReview = forceUpdateReview;

// Auto update saat ada perubahan di form
document.addEventListener('input', function() {
    const step5 = document.getElementById('step5');
    if (step5 && step5.classList.contains('active')) {
        clearTimeout(window.reviewTimeout);
        window.reviewTimeout = setTimeout(() => {
            updateReview();
        }, 300);
    }
});

// Update saat collapse/expand
document.addEventListener('shown.bs.collapse', function() {
    const step5 = document.getElementById('step5');
    if (step5 && step5.classList.contains('active')) {
        updateReview();
    }
});

// Panggil updateReview saat halaman siap
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const step5 = document.getElementById('step5');
        if (step5 && step5.classList.contains('active')) {
            updateReview();
        }
    }, 500);
});
</script>

<style>
.review-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.review-list .card {
    transition: all 0.2s ease;
}

.review-list .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
}

.table th, .table td {
    vertical-align: middle;
}

.rounded-4 {
    border-radius: 1rem !important;
}

/* Sembunyikan border default card */
.card {
    border: none !important;
}
</style>