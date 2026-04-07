@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('template.index') }}">Template Dokumen</a>
</li>
<li class="breadcrumb-item active text-success fw-semibold">
    Edit Data Template Dokumen
</li>
@endsection

@section('content')

<style>
.card-shadow {
    border: none;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
}
.placeholder-tag {
    color: #dc2626;
    font-weight: 600;
    font-family: monospace;
}
</style>

<div class="card card-shadow">
    <div class="card-header">
        <h5 class="text-success fw-semibold mb-0">
            Edit Template Dokumen
        </h5>
    </div>

    <div class="card-body">

        {{-- WARNING --}}
        <div class="alert alert-warning">
            <strong>Perhatian!</strong><br>
            Jangan mengubah placeholder seperti:
            <br>
            <span class="placeholder-tag">${nama_penerima}</span>,
            <span class="placeholder-tag">${nip_penerima}</span>,
            <span class="placeholder-tag">${sum_total}</span>, dll.
        </div>

        <form action="{{ route('template.update', $template->id) }}" 
              method="POST" 
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Nama --}}
            <div class="mb-3">
                <label class="form-label">Nama Template</label>
                <input type="text" name="nama"
                       class="form-control"
                       value="{{ old('nama', $template->nama) }}">
            </div>

            {{-- Jenis --}}
            <div class="mb-3">
                <label class="form-label">Jenis Dokumen</label>
                <select name="jenis" class="form-select">
                    <option value="kuitansi_spd"
                        {{ $template->jenis == 'kuitansi_spd' ? 'selected' : '' }}>
                        Kuitansi dan SPD
                    </option>
                    <option value="slip_gaji"
                        {{ $template->jenis == 'slip_gaji' ? 'selected' : '' }}>
                        Slip Gaji
                    </option>
                </select>
            </div>

            {{-- File Lama --}}
            <div class="mb-3">
                <label class="form-label">File Saat Ini</label><br>
                <a href="{{ asset('storage/'.$template->file_path) }}" target="_blank">
                    Lihat / Download File
                </a>
            </div>

            {{-- Upload Baru --}}
            <div class="mb-3">
                <label class="form-label">Ganti File (Opsional)</label>
                <input type="file" name="file" class="form-control" accept=".docx">
                <small class="text-muted">
                    Kosongkan jika tidak ingin mengganti file
                </small>
            </div>

            {{-- Submit --}}
            <div class="text-end">
                <a href="{{ route('template.index') }}" class="btn btn-secondary">
                    Kembali
                </a>

                <button class="btn btn-success">
                    Update
                </button>
            </div>

        </form>

    </div>
</div>

@endsection