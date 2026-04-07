@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('template.index') }}">Template Dokumen</a>
</li>
<li class="breadcrumb-item active text-success fw-semibold">
    Upload Template Dokumen
</li>
@endsection

@section('content')

<style>
.card-shadow {
    border: none;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
}

/* Styling placeholder biar beda 🔥 */
.placeholder-tag {
    color: #dc2626;
    font-weight: 600;
    font-family: monospace;
}
</style>

<div class="card card-shadow">
    <div class="card-header">
        <h5 class="text-success fw-semibold mb-0">
            Upload Template Dokumen
        </h5>
    </div>

    <div class="card-body">

        {{-- DOWNLOAD CONTOH TEMPLATE --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Contoh Template</label><br>

            <a href="{{ asset('storage/templates/contoh_template_kuitansi_spd.docx') }}" 
               class="btn btn-outline-success btn-sm me-2" 
               target="_blank">
               Download Kuitansi & SPD
            </a>

            <a href="{{ asset('templates/contoh_slip_gaji.docx') }}" 
               class="btn btn-outline-primary btn-sm" 
               target="_blank">
               Download Slip Gaji
            </a>
        </div>

        {{-- ⚠️ WARNING --}}
        <div class="alert alert-warning">
            <strong>Perhatian!</strong><br>
            Template hanya boleh diubah pada bagian 
            <b>logo, layout, dan format tampilan</b>.<br><br>

            Jangan mengubah atau menghapus placeholder seperti:
            <br>
            <span class="placeholder-tag">${nama_penerima}</span>,
            <span class="placeholder-tag">${nip_penerima}</span>,
            <span class="placeholder-tag">${nomor_spd}</span>,
            <span class="placeholder-tag">${tanggal_spd}</span>,
            <span class="placeholder-tag">${sum_total}</span>,
            <span class="placeholder-tag">${no}</span>,
            <span class="placeholder-tag">${uraian}</span>,
            <span class="placeholder-tag">${jumlah}</span>, dll.
            <br><br>

            Jika placeholder diubah, sistem tidak dapat mengisi data otomatis.
        </div>

        <form action="{{ route('template.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Nama --}}
            <div class="mb-3">
                <label class="form-label">Nama Template</label>
                <input type="text" name="nama"
                       class="form-control"
                       value="{{ old('nama') }}"
                       placeholder="Contoh: Template Kuitansi 2026">
            </div>

            {{-- Jenis --}}
            <div class="mb-3">
                <label class="form-label">Jenis Dokumen</label>
                <select name="jenis" class="form-select">
                    <option value="">-- Pilih Jenis --</option>
                    <option value="kuitansi_spd">Kuitansi dan SPD</option>
                    <option value="slip_gaji">Slip Gaji</option>
                </select>
            </div>

            {{-- File --}}
            <div class="mb-3">
                <label class="form-label">File Template (.docx)</label>
                <input type="file" name="file" class="form-control" accept=".docx">
            </div>

            {{-- INFO TAMBAHAN --}}
            <div class="alert alert-warning small">
                Gunakan placeholder dengan format:
                <br>
                <span class="placeholder-tag">${nama_penerima}</span>,
                <span class="placeholder-tag">${nip_penerima}</span>,
                <span class="placeholder-tag">${sum_total}</span>, dll.
            </div>

            {{-- Submit --}}
            <div class="text-end">
                <a href="{{ route('template.index') }}" class="btn btn-secondary">
                    Kembali
                </a>

                <button class="btn btn-success">
                    Upload
                </button>
            </div>

        </form>

    </div>
</div>

@endsection