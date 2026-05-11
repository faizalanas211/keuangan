{{-- Modal Edit Pejabat --}}
<div class="modal fade" id="modalEditPejabat" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg" style="background: white;">
            <div class="modal-header border-0 pt-4 px-4" style="background: white;">
                <h5 class="modal-title fw-semibold" style="color: #2d6a4f;">
                    <i class="fas fa-user-edit me-2" style="color: #40916c;"></i> Edit Pejabat Periode
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-0" style="background: white;">
                <form id="formEditPejabat" action="" method="POST">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">

                    {{-- JENIS PEJABAT --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: #374151;">
                            Jenis Pejabat <span class="text-danger">*</span>
                        </label>
                        <select name="jenis_pejabat_id" id="edit_jenis_pejabat_id" class="form-select" style="border-radius: 10px; border-color: #e5e7eb; background: white;" required>
                            <option value="">-- Pilih Jenis --</option>
                            @foreach($jenisPejabat as $j)
                                <option value="{{ $j->id }}">
                                    {{ $j->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- PEGAWAI --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: #374151;">
                            Pegawai <span class="text-danger">*</span>
                        </label>
                        <select name="pegawai_id" id="edit_pegawai_id" class="form-select" style="border-radius: 10px; border-color: #e5e7eb; background: white;" required>
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach($pegawai as $p)
                                <option value="{{ $p->id }}" data-jabatan="{{ $p->jabatan }}">
                                    {{ $p->nama }} - {{ $p->jabatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- PERIODE --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold" style="color: #374151;">
                                Periode Mulai <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="periode_mulai" id="edit_periode_mulai" class="form-control" style="border-radius: 10px; border-color: #e5e7eb; background: white;" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold" style="color: #374151;">
                                Periode Selesai <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="periode_selesai" id="edit_periode_selesai" class="form-control" style="border-radius: 10px; border-color: #e5e7eb; background: white;" required>
                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: #374151;">Status</label>
                        <select name="is_active" id="edit_is_active" class="form-select" style="border-radius: 10px; border-color: #e5e7eb; background: white;">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>

                </form>
            </div>
            <div class="modal-footer border-0 pb-4 px-4" style="background: white;">
                <button type="button" class="btn btn-light rounded-pill px-4" style="border: 1px solid #e5e7eb; background: white;" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="button" class="btn rounded-pill px-4" id="submitEditPejabat" style="background: linear-gradient(135deg, #2d6a4f 0%, #40916c 100%); color: white; border: none;">
                    <i class="fas fa-save me-1"></i> Update
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tom Select untuk dropdown pegawai di modal edit
    if (typeof TomSelect !== 'undefined') {
        new TomSelect("#edit_pegawai_id", {
            create: false,
            sortField: { field: "text", direction: "asc" }
        });
    }
    
    // Submit form edit via AJAX
    document.getElementById('submitEditPejabat')?.addEventListener('click', async function() {
    const form = document.getElementById('formEditPejabat');
    
    const btn = this;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
    btn.disabled = true;
    
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new FormData(form) // FormData otomatis include _token dan _method
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Data pejabat berhasil diupdate', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditPejabat'));
            modal.hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message || 'Gagal mengupdate data', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Terjadi kesalahan', 'error');
    } finally {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    }
});
});

// Fungsi untuk membuka modal edit dengan data
function openEditModal(id, jenisPejabatId, pegawaiId, periodeMulai, periodeSelesai, isActive) {
    console.log('openEditModal called with:', {
        id, jenisPejabatId, pegawaiId, periodeMulai, periodeSelesai, isActive
    });
    
    // Set form action
    const form = document.getElementById('formEditPejabat');
    form.action = `/dashboard/pejabat/${id}`;
    
    // Set nilai form
    document.getElementById('edit_jenis_pejabat_id').value = jenisPejabatId;
    document.getElementById('edit_pegawai_id').value = pegawaiId;
    document.getElementById('edit_periode_mulai').value = periodeMulai;
    document.getElementById('edit_periode_selesai').value = periodeSelesai;
    document.getElementById('edit_is_active').value = isActive;
    
    console.log('Form action set to:', form.action);
    console.log('Periode Mulai value:', document.getElementById('edit_periode_mulai').value);
    console.log('Periode Selesai value:', document.getElementById('edit_periode_selesai').value);
    
    // Buka modal
    const modal = new bootstrap.Modal(document.getElementById('modalEditPejabat'));
    modal.show();
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? '#2d6a4f' : '#dc2626';
    toast.className = `toast-notification text-white position-fixed top-0 end-0 m-3 rounded-3 shadow-lg`;
    toast.style.zIndex = '9999';
    toast.style.padding = '12px 20px';
    toast.style.minWidth = '250px';
    toast.style.backgroundColor = bgColor;
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2 fs-5"></i>
            <div>${message}</div>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>