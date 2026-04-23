@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('perjadin.index') }}" class="text-decoration-none">Data Perjalanan Dinas</a>
</li>
<li class="breadcrumb-item active text-success fw-semibold">Detail Perjalanan</li>
@endsection

@section('content')

<style>
    .card-shadow {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: box-shadow 0.2s ease;
    }
    
    .card-shadow:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }
    
    .section-title {
        font-weight: 600;
        font-size: 1.1rem;
        color: #16a34a;
        border-left: 4px solid #16a34a;
        padding-left: 12px;
        margin-bottom: 1.25rem;
    }
    
    .info-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 4px;
    }
    
    .info-value {
        font-weight: 500;
        color: #1f2937;
    }
    
    .kelompok-header {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        padding: 1rem 1.25rem;
        border-radius: 12px;
        margin-bottom: 1.25rem;
    }
    
    .badge-st {
        background-color: #eef2ff;
        color: #1e40af;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: #f0fdf4;
        color: #16a34a;
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(22, 163, 74, 0.25);
    }
    
    .total-card {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        border-radius: 16px;
        color: white;
    }
    
    .table-detail th {
        background-color: #f8fafc;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table-detail td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
</style>

<div class="container-fluid px-0">
    
    {{-- ================= HEADER ================= --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-success mb-1">
                <i class="fas fa-info-circle"></i>Detail Perjalanan Dinas
            </h4>
            <p class="text-muted mb-0">Informasi lengkap perjalanan dinas dan rincian biaya</p>
        </div>
        <a href="{{ route('perjadin.export.nominatif', $perjalanan->id) }}" 
           class="btn btn-success shadow-sm">
            <i class="fas fa-file-excel me-2"></i> Export Nominatif
        </a>
    </div>

    {{-- ================= INFORMASI PERJALANAN ================= --}}
    <div class="card card-shadow mb-4">
        <div class="card-body p-4">
            <h5 class="section-title">
                <i class="fas fa-briefcase me-2"></i>Informasi Perjalanan
            </h5>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="info-label">Tingkat Perjalanan</div>
                            <div class="info-value">{{ $perjalanan->tingkat_perjalanan ?? '-' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Alat Angkutan</div>
                            <div class="info-value">{{ $perjalanan->alat_angkutan ?? '-' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Kota Asal</div>
                            <div class="info-value">{{ $perjalanan->dari_kota ?? '-' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Kota Tujuan</div>
                            <div class="info-value">{{ $perjalanan->tujuan_kota ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="info-label">Tanggal Mulai</div>
                            <div class="info-value">
                                {{ \Carbon\Carbon::parse($perjalanan->tanggal_mulai)->format('d F Y') }}
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Tanggal Akhir</div>
                            <div class="info-value">
                                {{ \Carbon\Carbon::parse($perjalanan->tanggal_akhir)->format('d F Y') }}
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Tanggal Terima</div>
                            <div class="info-value">
                                {{ \Carbon\Carbon::parse($perjalanan->tanggal_terima)->format('d F Y') }}
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Lama Perjalanan</div>
                            <div class="info-value">
                                @php
                                    $start = \Carbon\Carbon::parse($perjalanan->tanggal_mulai);
                                    $end = \Carbon\Carbon::parse($perjalanan->tanggal_akhir);
                                    $days = $start->diffInDays($end) + 1;
                                @endphp
                                {{ $days }} Hari
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <hr class="my-2">
                    <div class="info-label">Nama Kegiatan</div>
                    <div class="info-value mt-1">{{ $perjalanan->nama_kegiatan ?? '-' }}</div>
                </div>

                @if($perjalanan->kode_mak || $perjalanan->akun_biaya)
                <div class="col-12">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-label">Kode MAK</div>
                            <div class="info-value">{{ $perjalanan->kode_mak ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Akun Biaya</div>
                            <div class="info-value">{{ $perjalanan->akun_biaya ?? '-' }}</div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ================= DATA PESERTA PER KELOMPOK ================= --}}
    <h5 class="fw-semibold mb-3" style="color: #1b5e20;">
        <i class="fas fa-users me-2"></i>Data Peserta Per Kelompok
    </h5>

    @forelse($perjalanan->kelompokPerjalanan as $kIndex => $kelompok)
    <div class="card card-shadow mb-4">
        <div class="card-body p-0">
            
            {{-- Header Kelompok --}}
            <div class="kelompok-header d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold text-success">
                        <i class="fas fa-layer-group me-2"></i>
                        {{ strtoupper($kelompok->nama_kelompok) }}
                    </h5>
                </div>
                <div class="d-flex gap-3">
                    <div class="badge-st" style="background-color: #e3ffe8; color: #1b5e20;">
                        <i class="far fa-file-alt me-1"></i>
                        <strong>No ST:</strong> {{ $kelompok->nomor_st ?? '-' }}
                    </div>
                    <div class="badge-st" style="background-color: #e3ffe8; color: #1b5e20;">
                        <i class="far fa-calendar-alt me-1"></i>
                        <strong>Tanggal ST:</strong>
                        {{ $kelompok->tanggal_st ? \Carbon\Carbon::parse($kelompok->tanggal_st)->format('d/m/Y') : '-' }}
                    </div>
                </div>
            </div>

            <div class="p-4 pt-0">
                {{-- PEGAWAI --}}
                @if($kelompok->pegawai && $kelompok->pegawai->count())
                <div class="mb-4">
                    <h6 class="fw-semibold text-primary mb-3">
                        <i class="fas fa-user-tie me-2"></i>Pegawai
                    </h6>

                    <div class="accordion" id="accordionPegawai{{ $kIndex }}">
                        @foreach($kelompok->pegawai as $index => $pp)
                        @php $grandTotal = $pp->rincian->sum('total'); @endphp
                        
                        <div class="accordion-item mb-2 border rounded overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-3" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#pegawai{{ $kIndex }}{{ $index }}">
                                    <div class="d-flex justify-content-between w-100 me-3">
                                        <span>
                                            <i class="fas fa-user me-2"></i>
                                            <strong>{{ $pp->pegawai->nama }}</strong>
                                            <span class="text-muted ms-2">({{ $pp->pegawai->nip }})</span>
                                        </span>
                                        <span class="text-success fw-semibold">
                                            Rp{{ number_format($grandTotal, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </button>
                            </h2>

                            <div id="pegawai{{ $kIndex }}{{ $index }}" 
                                 class="accordion-collapse collapse">
                                <div class="accordion-body p-0">
                                    @include('dashboard.perjadin.partials.table-rincian', ['rincian' => $pp->rincian, 'total' => $grandTotal])
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- NON PEGAWAI --}}
                @if($kelompok->nonpegawai && $kelompok->nonpegawai->count())
                <div>
                    <h6 class="fw-semibold text-success mb-3">
                        <i class="fas fa-user-friends me-2"></i>Non Pegawai
                    </h6>

                    <div class="accordion" id="accordionNP{{ $kIndex }}">
                        @foreach($kelompok->nonpegawai as $index => $np)
                        @php $grandTotal = $np->rincian->sum('total'); @endphp
                        
                        <div class="accordion-item mb-2 border rounded overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-3" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#np{{ $kIndex }}{{ $index }}">
                                    <div class="d-flex justify-content-between w-100 me-3">
                                        <span>
                                            <i class="fas fa-user me-2"></i>
                                            <strong>{{ $np->nama }}</strong>
                                            @if($np->instansi)
                                            <span class="text-muted ms-2">({{ $np->instansi }})</span>
                                            @endif
                                        </span>
                                        <span class="text-success fw-semibold">
                                            Rp{{ number_format($grandTotal, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </button>
                            </h2>

                            <div id="np{{ $kIndex }}{{ $index }}" 
                                 class="accordion-collapse collapse">
                                <div class="accordion-body p-0">
                                    @include('dashboard.perjadin.partials.table-rincian', ['rincian' => $np->rincian, 'total' => $grandTotal])
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>Belum ada data kelompok perjalanan.
    </div>
    @endforelse

    {{-- ================= TOTAL KESELURUHAN ================= --}}
    <div class="total-card p-4 mt-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h6 class="mb-1 text-white">Total Keseluruhan</h6>
                <p class="mb-0 small text-white-80">Seluruh biaya perjalanan dinas</p>
            </div>
            <div class="text-end">
                <div class="fw-bold text-white" style="font-size: 1.5rem;">
                    Rp{{ number_format($grandTotalPerjalanan ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

</div>

@endsection