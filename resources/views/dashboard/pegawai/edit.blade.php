@extends('layouts.admin')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('pegawai.index') }}">Data Pegawai</a>
    </li>
    <li class="breadcrumb-item active text-primary fw-semibold">
        Edit Pegawai
    </li>
@endsection

@section('content')
<div class="row">
    <div class="col-xxl">
        <div class="card mb-4">

            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Edit Data Pegawai</h5>
                <a href="{{ route('pegawai.index', ['page' => request('page')]) }}"
                   class="btn btn-secondary btn-sm">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>

            <div class="card-body">
                <form action="{{ route('pegawai.update', ['pegawai' => $pegawai->id, 'page' => request('page')]) }}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="page" value="{{ request('page') }}">

                    {{-- NAMA --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Nama Pegawai</label>
                        <div class="col-sm-10">
                            <input type="text"
                                   name="nama"
                                   class="form-control @error('nama') is-invalid @enderror"
                                   value="{{ old('nama', $pegawai->nama) }}"
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
                                <option value="aktif" {{ old('status', $pegawai->status) == 'aktif' ? 'selected' : '' }}>
                                    Aktif
                                </option>
                                <option value="nonaktif" {{ old('status', $pegawai->status) == 'nonaktif' ? 'selected' : '' }}>
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
                                   value="{{ old('nip', $pegawai->nip) }}"
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
                            <select name="jenis_kelamin"
                                    class="form-select @error('jenis_kelamin') is-invalid @enderror">
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="Laki-laki"
                                    {{ old('jenis_kelamin', $pegawai->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>
                                    Laki-laki
                                </option>
                                <option value="Perempuan"
                                    {{ old('jenis_kelamin', $pegawai->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>
                                    Perempuan
                                </option>
                            </select>
                            @error('jenis_kelamin')
                                <div class="invalid-feedback">{{ $message }}</div>
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
                                   value="{{ old('jabatan', $pegawai->jabatan) }}"
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
                                   value="{{ old('pangkat_golongan', $pegawai->pangkat_golongan) }}"
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

                            @if ($pegawai->foto)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $pegawai->foto) }}"
                                         class="img-thumbnail"
                                         style="max-width:150px"
                                         alt="Foto Pegawai">
                                </div>
                            @endif

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
@endsection
