@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('perjadin.index') }}">Data Perjalanan Dinas</a>
</li>
<li class="breadcrumb-item active text-success fw-semibold">
    Detail Perjalanan
</li>
@endsection

@section('content')

<style>
.card-shadow {
    border: none;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
}
.section-title {
    font-weight: 600;
    color: #16a34a;
}
.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 10px;
}
</style>

{{-- ================= HEADER DENGAN TOMBOL EXPORT ================= --}}
<div class="header-section">
    <h4 class="mb-0 fw-semibold text-success">Detail Perjalanan Dinas</h4>
    <a href="{{ route('perjadin.export.nominatif', $perjalanan->id) }}" 
       class="btn btn-success">
        <i class="fas fa-file-excel me-1"></i> Export Nominatif
    </a>
</div>

{{-- ================= INFORMASI ================= --}}
<div class="card card-shadow mb-4">
    <div class="card-body">

        <h5 class="section-title mb-3">Informasi Perjalanan</h5>

        <div class="row mb-2">
            <div class="col-md-6"><strong>Tingkat:</strong> {{ $perjalanan->tingkat_perjalanan }}</div>
            <div class="col-md-6"><strong>Alat Angkutan:</strong> {{ $perjalanan->alat_angkutan }}</div>
        </div>

        <div class="row mb-2">
            <div class="col-md-6"><strong>Dari:</strong> {{ $perjalanan->dari_kota }}</div>
            <div class="col-md-6"><strong>Tujuan:</strong> {{ $perjalanan->tujuan_kota }}</div>
        </div>

        <div class="row mb-2">
            <div class="col-md-6">
                <strong>Tanggal Pelaksanaan:</strong>
                {{ \Carbon\Carbon::parse($perjalanan->tanggal_mulai)->format('d-m-Y') }}
                s/d
                {{ \Carbon\Carbon::parse($perjalanan->tanggal_akhir)->format('d-m-Y') }}
            </div>
            <div class="col-md-6">
                <strong>Tanggal Terima:</strong>
                {{ \Carbon\Carbon::parse($perjalanan->tanggal_terima)->format('d-m-Y') }}
            </div>
        </div>

        <div class="mt-2">
            <strong>Kegiatan:</strong>
            <p class="mb-1">{{ $perjalanan->nama_kegiatan }}</p>
        </div>

    </div>
</div>

{{-- ================= SURAT ================= --}}
<div class="card card-shadow mb-4">
    <div class="card-body">

        <h5 class="section-title mb-3">Surat Perjalanan</h5>

        <div class="row">
            <div class="col-md-4">
                <strong>Nomor ST:</strong><br>
                {{ $perjalanan->surat->nomor_st ?? '-' }}
            </div>
            <div class="col-md-4">
                <strong>Tanggal ST:</strong><br>
                {{ $perjalanan->surat->tanggal_st 
                    ? \Carbon\Carbon::parse($perjalanan->surat->tanggal_st)->format('d-m-Y') 
                    : '-' 
                }}
            </div>
            
            <div class="col-md-4">
                <strong>Nomor SK:</strong><br>
                {{ $perjalanan->surat->nomor_sk ?? '-' }}
            </div>
        </div>

    </div>
</div>

{{-- ================= PEGAWAI & RINCIAN (ACCORDION) ================= --}}
<div class="accordion" id="accordionPegawai">

@foreach($perjalanan->pegawaiPerjalanan as $index => $pp)

@php
    $grandTotal = 0;
@endphp

<div class="accordion-item mb-3 card-shadow">
    <h2 class="accordion-header" id="heading{{ $index }}">
        <button class="accordion-button collapsed fw-semibold" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapse{{ $index }}"
                aria-expanded="false"
                aria-controls="collapse{{ $index }}">
            
            👤 {{ $pp->pegawai->nama }} ({{ $pp->pegawai->nip }}) - {{ $pp->pegawai->jabatan }}
        </button>
    </h2>

    <div id="collapse{{ $index }}"
         class="accordion-collapse collapse"
         aria-labelledby="heading{{ $index }}"
         data-bs-parent="#accordionPegawai">

        <div class="accordion-body">

            <div class="mb-3 d-flex gap-2">
                <a href="{{ route('perjadin.export.kuitansi', $pp->id) }}"
                   class="btn btn-sm btn-success">
                   Export Kuitansi & SPD
                </a>
                <a href="{{ route('perjadin.export.sby', $pp->id) }}"
                   class="btn btn-sm btn-primary">
                   Export SBY
                </a>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Jenis Biaya</th>
                        <th>Volume</th>
                        <th>Satuan</th>
                        <th>Tarif</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pp->rincian as $r)
                        @php $grandTotal += $r->total; @endphp
                        <tr>
                            <td>{{ $r->jenisBiaya->nama_biaya }}</td>
                            <td>{{ (int) $r->volume }}</td>
                            <td>{{ $r->satuan }}</td>
                            <td>Rp{{ number_format($r->tarif,0,',','.') }}</td>
                            <td>Rp{{ number_format($r->total,0,',','.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="text-end">
                <strong>
                    Total Per Pegawai:
                    Rp{{ number_format($grandTotal,0,',','.') }}
                </strong>
            </div>

        </div>
    </div>
</div>

@endforeach
</div>

<div class="card card-shadow mt-4">
    <div class="card-body text-end">

        <h5 class="fw-bold text-success">
            Total Keseluruhan Perjalanan:
        </h5>

        <h4 class="fw-bold">
            Rp{{ number_format($grandTotalPerjalanan,0,',','.') }}
        </h4>

    </div>
</div>

@endsection