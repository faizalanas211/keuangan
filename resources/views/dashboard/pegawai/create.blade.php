@extends('layouts.admin')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('pegawai.index') }}">Data Pegawai</a>
    </li>
    <li class="breadcrumb-item active text-primary fw-semibold">
        Tambah Pegawai
    </li>
@endsection

@section('content')
<div class="row">
    <div class="col-xxl">
        <div class="card mb-4">

            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Tambah Data Pegawai</h5>
                <button class="btn btn-primary mx-2" data-bs-toggle="modal" data-bs-target="#importModal">
                    Impor Excel
                </button>
            </div>

            <div class="card-body">
                <form action="{{ route('pegawai.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- NAMA --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Nama</label>
                        <div class="col-sm-10">
                            <input type="text"
                                   name="nama"
                                   class="form-control @error('nama') is-invalid @enderror"
                                   value="{{ old('nama') }}"
                                   autocomplete="off">
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Status</label>
                        <div class="col-sm-10">
                            <select name="status" class="form-select">
                                <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>
                                    Aktif
                                </option>
                                <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>
                                    Nonaktif
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- NIP --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">NIP</label>
                        <div class="col-sm-10">
                            <input type="text"
                                   name="nip"
                                   class="form-control @error('nip') is-invalid @enderror"
                                   value="{{ old('nip') }}"
                                   autocomplete="off">
                            @error('nip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- JENIS KELAMIN --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Jenis Kelamin</label>
                        <div class="col-sm-10">
                            <select name="jenis_kelamin" class="form-select">
                                <option value="" disabled selected>Pilih Jenis Kelamin</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>
                                    Laki-laki
                                </option>
                                <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>
                                    Perempuan
                                </option>
                            </select>
                            @error('jenis_kelamin')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- JABATAN --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Jabatan</label>
                        <div class="col-sm-10">
                            <input type="text"
                                   name="jabatan"
                                   class="form-control @error('jabatan') is-invalid @enderror"
                                   value="{{ old('jabatan') }}"
                                   autocomplete="off">
                            @error('jabatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- PANGKAT / GOLONGAN --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Pangkat, Golongan</label>
                        <div class="col-sm-10">
                            <input type="text"
                                   name="pangkat_golongan"
                                   class="form-control @error('pangkat_golongan') is-invalid @enderror"
                                   value="{{ old('pangkat_golongan') }}"
                                   autocomplete="off">
                            @error('pangkat_golongan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- FOTO --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Foto</label>
                        <div class="col-sm-10">
                            <input type="file"
                                   name="foto"
                                   class="form-control @error('foto') is-invalid @enderror"
                                   accept=".jpg,.jpeg,.png">
                            <div class="form-text">
                                JPG/JPEG/PNG • Maks 10 MB
                            </div>
                            @error('foto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- ACTION --}}
                    <div class="row justify-content-end">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary">
                                Simpan
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL IMPORT EXCEL --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">

            <div class="modal-header">
                <h5 class="modal-title">Impor Data Pegawai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('pegawai.import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-body">
                    <label class="form-label fw-semibold">Pilih File Excel</label>
                    <input type="file"
                           name="file"
                           accept=".xlsx,.xls"
                           class="form-control @error('file') is-invalid @enderror">

                    <div class="form-text mt-1">
                        Format .xlsx / .xls • Maks 2 MB
                    </div>

                    <div class="mt-2">
                        <a href="{{ asset('template/template_data_pegawai.xlsx') }}"
                           class="text-primary fw-semibold"
                           download>
                            📥 Download Template Excel
                        </a>
                    </div>

                    @error('file')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Impor
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
