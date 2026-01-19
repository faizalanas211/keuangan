@extends('layouts.admin')

@section('breadcrumb')
    <li class="breadcrumb-item">Keuangan</li>
    <li class="breadcrumb-item">
        <a href="{{ route('penghasilan.index') }}">Penghasilan Pegawai</a>
    </li>
    <li class="breadcrumb-item active text-primary fw-semibold">
        Edit Penghasilan
    </li>
@endsection

@section('content')
<div class="row">
    <div class="col-xxl">
        <div class="card mb-4">

            {{-- HEADER --}}
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Edit Penghasilan Pegawai</h5>
                <a href="{{ route('penghasilan.index') }}"
                   class="btn btn-secondary btn-sm">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>

            {{-- BODY --}}
            <div class="card-body">
                <form action="{{ route('penghasilan.update', $penghasilan->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- PEGAWAI --}}
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Pegawai</label>
                        <div class="col-sm-9">
                            <select name="pegawai_id"
                                    class="form-select @error('pegawai_id') is-invalid @enderror">
                                <option value="">-- Pilih Pegawai --</option>
                                @foreach ($pegawais as $pegawai)
                                    <option value="{{ $pegawai->id }}"
                                        {{ old('pegawai_id', $penghasilan->pegawai_id) == $pegawai->id ? 'selected' : '' }}>
                                        {{ $pegawai->nama }} - {{ $pegawai->nip }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pegawai_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- TANGGAL / PERIODE --}}
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Periode (Bulan)</label>
                        <div class="col-sm-9">
                            <input type="date"
                                   name="tanggal"
                                   class="form-control @error('tanggal') is-invalid @enderror"
                                   value="{{ old('tanggal', $penghasilan->tanggal) }}">
                            @error('tanggal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- GAJI INDUK --}}
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Gaji Induk</label>
                        <div class="col-sm-9">
                            <input type="number"
                                   name="gaji_induk"
                                   id="gaji_induk"
                                   class="form-control hitung @error('gaji_induk') is-invalid @enderror"
                                   value="{{ old('gaji_induk', $penghasilan->gaji_induk) }}">
                            @error('gaji_induk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- TUNJANGAN --}}
                    @php
                        $fields = [
                            'tunj_suami_istri' => 'Tunj. Suami / Istri',
                            'tunj_anak' => 'Tunj. Anak',
                            'tunj_umum' => 'Tunj. Umum',
                            'tunj_struktural' => 'Tunj. Struktural',
                            'tunj_fungsional' => 'Tunj. Fungsional',
                            'tunj_beras' => 'Tunj. Beras',
                            'tunj_pajak' => 'Tunj. Pajak',
                            'pembulatan' => 'Pembulatan',
                        ];
                    @endphp

                    @foreach ($fields as $name => $label)
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">{{ $label }}</label>
                        <div class="col-sm-9">
                            <input type="number"
                                   name="{{ $name }}"
                                   class="form-control hitung @error($name) is-invalid @enderror"
                                   value="{{ old($name, $penghasilan->$name) }}">
                            @error($name)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    @endforeach

                    {{-- TOTAL PENGHASILAN --}}
                    <div class="row mb-4">
                        <label class="col-sm-3 col-form-label fw-bold">
                            Total Penghasilan
                        </label>
                        <div class="col-sm-9">
                            <input type="text"
                                   id="total_penghasilan"
                                   class="form-control fw-bold text-success"
                                   readonly
                                   value="{{ number_format($penghasilan->total_penghasilan, 0, ',', '.') }}">
                            <small class="text-muted">
                                Total dihitung otomatis
                            </small>
                        </div>
                    </div>

                    {{-- ACTION --}}
                    <div class="row justify-content-end">
                        <div class="col-sm-9">
                            <button type="submit" class="btn btn-primary">
                                Simpan Perubahan
                            </button>
                            <a href="{{ route('penghasilan.index') }}"
                               class="btn btn-light ms-2">
                                Batal
                            </a>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT HITUNG TOTAL --}}
<script>
    function hitungTotal() {
        let total = 0;
        document.querySelectorAll('.hitung').forEach(function (input) {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('total_penghasilan').value =
            total.toLocaleString('id-ID');
    }

    document.querySelectorAll('.hitung').forEach(function (input) {
        input.addEventListener('input', hitungTotal);
    });

    // Hitung awal
    hitungTotal();
</script>
@endsection
