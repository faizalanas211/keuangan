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
    
    /* Button group styling */
    .btn-group-export {
        gap: 8px;
    }
    
    .btn-export {
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .btn-export:hover {
        transform: translateY(-1px);
    }
    
    /* ========== PERBAIKAN CARD TIDAK BERTUMPUK ========== */
    .sub-kelompok-card {
        margin-bottom: 1.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    
    .sub-kelompok-card:last-child {
        margin-bottom: 0;
    }
    
    .sub-kelompok-card:hover {
        border-color: #cbd5e1;
    }
    
    .sub-kelompok-header {
        background: #f8fafc;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .accordion-custom .accordion-item {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        margin-bottom: 0.75rem;
        overflow: hidden;
    }
    
    .accordion-custom .accordion-item:last-child {
        margin-bottom: 0;
    }
    
    .accordion-custom .accordion-button {
        background: white;
        padding: 0.875rem 1.25rem;
        font-weight: 500;
    }
    
    .accordion-custom .accordion-button:not(.collapsed) {
        background: #f0fdf4;
        color: #16a34a;
    }
    
    .btn-export-group {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    @media (max-width: 768px) {
        .btn-export-group {
            justify-content: flex-start;
        }
        
        .sub-kelompok-header {
            flex-direction: column;
            gap: 12px;
        }
        
        .sub-kelompok-header .badge {
            align-self: flex-start;
        }
    }
</style>

<div class="container-fluid px-0">
    
    {{-- ================= HEADER ================= --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-success mb-1">
                <i class="fas fa-info-circle me-2"></i>Detail Perjalanan Dinas
            </h4>
            <p class="text-muted mb-0">Informasi lengkap perjalanan dinas dan rincian biaya</p>
        </div>
        
        {{-- Tombol Export Massal - Versi Ringkas --}}
        <div class="dropdown mt-2 mt-sm-0">
            <button class="btn btn-success dropdown-toggle px-4 py-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-download me-2"></i>Export Data
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li>
                    <a class="dropdown-item py-2" href="{{ route('perjadin.export.nominatif', $perjalanan->id) }}">
                        <i class="fas fa-file-alt me-2 text-success"></i>
                        <span>Export Nominatif</span>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item py-2" href="{{ route('perjadin.export.sby.zip', $perjalanan->id) }}">
                        <i class="fas fa-file-archive me-2 text-primary"></i>
                        <span>Download Semua SBY (ZIP)</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="{{ route('perjadin.export.kuitansi.zip', $perjalanan->id) }}">
                        <i class="fas fa-file-archive me-2 text-primary"></i>
                        <span>Download Semua Kuitansi (ZIP)</span>
                    </a>
                </li>
            </ul>
        </div>
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
                                {{ \Carbon\Carbon::parse($perjalanan->tanggal_mulai)->translatedFormat('d F Y') }}
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Tanggal Akhir</div>
                            <div class="info-value">
                                {{ \Carbon\Carbon::parse($perjalanan->tanggal_akhir)->translatedFormat('d F Y') }}
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Tanggal Terima</div>
                            <div class="info-value">
                                {{ \Carbon\Carbon::parse($perjalanan->tanggal_terima)->translatedFormat('d F Y') }}
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
    
    <ul class="nav nav-tabs mb-3" id="kelompokTab" role="tablist">
        @foreach($perjalanan->kelompokPerjalanan as $kIndex => $kelompok)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $kIndex == 0 ? 'active' : '' }}"
                id="tab-{{ $kIndex }}"
                data-bs-toggle="tab"
                data-bs-target="#content-{{ $kIndex }}"
                type="button">
                <i class="fas fa-users me-1"></i>
                {{ strtoupper($kelompok->nama_kelompok) }}
            </button>
        </li>
        @endforeach
    </ul>

    <div class="tab-content">
        @foreach($perjalanan->kelompokPerjalanan as $kIndex => $kelompok)
        <div class="tab-pane fade {{ $kIndex == 0 ? 'show active' : '' }}" 
             id="content-{{ $kIndex }}">
            
            <div class="card card-shadow">
                <div class="card-body p-4">
                    @forelse($kelompok->subKelompok as $sIndex => $sub)
                        @php
                            $totalST = $sub->pegawai->sum(fn($pp) => $pp->rincian->sum('total')) 
                                     + $sub->nonpegawai->sum(fn($np) => $np->rincian->sum('total'));
                        @endphp

                        {{-- Sub Kelompok Card --}}
                        <div class="sub-kelompok-card">
                            {{-- Header ST --}}
                            <div class="sub-kelompok-header d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <h6 class="fw-bold mb-1 text-primary">
                                        <i class="fas fa-ticket-alt me-2"></i>
                                        No ST: {{ $sub->nomor_st ?? '-' }}
                                    </h6>
                                    <small class="text-muted">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        {{ $sub->tanggal_st ? \Carbon\Carbon::parse($sub->tanggal_st)->translatedFormat('d F Y') : '-' }}
                                    </small>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    <a href="{{ route('perjadin.export.kuitansi.st', $sub->id) }}" 
                                        class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-envelope me-1"></i> Export Kuitansi ST
                                    </a>
                                    <a href="{{ route('perjadin.export.amplop.st', $sub->id) }}" 
                                        class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-envelope me-1"></i> Export Amplop ST
                                    </a>
                                    <span class="badge bg-success fs-6 px-3 py-2">
                                        <i class="fas fa-rupiah-sign me-1"></i>
                                        {{ number_format($totalST, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Body Sub Kelompok --}}
                            <div class="p-3">
                                {{-- PEGAWAI --}}
                                @if($sub->pegawai->count())
                                <div class="mb-4">
                                    <h6 class="fw-semibold text-primary mb-3">
                                        <i class="fas fa-user-tie me-2"></i>Pegawai 
                                    </h6>
                                    <div class="accordion accordion-custom" id="pegawaiAccordion{{ $kIndex }}{{ $sIndex }}">
                                        @foreach($sub->pegawai as $pIndex => $pp)
                                        @php $total = $pp->rincian->sum('total'); @endphp
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" 
                                                    type="button"
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#pegawai{{ $kIndex }}{{ $sIndex }}{{ $pIndex }}">
                                                    <div class="d-flex justify-content-between w-100 me-3 align-items-center">
                                                        <span>
                                                            <i class="fas fa-user-circle me-2"></i>
                                                            {{ $pp->pegawai->nama }}
                                                            <small class="text-muted ms-2">
                                                                ({{ $pp->pegawai->nip }})
                                                            </small>
                                                        </span>
                                                        <span class="text-success fw-semibold">
                                                            Rp{{ number_format($total, 0, ',', '.') }}
                                                        </span>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="pegawai{{ $kIndex }}{{ $sIndex }}{{ $pIndex }}" 
                                                 class="accordion-collapse collapse" 
                                                 data-bs-parent="#pegawaiAccordion{{ $kIndex }}{{ $sIndex }}">
                                                <div class="accordion-body p-0">
                                                    @include('dashboard.perjadin.partials.table-rincian', [
                                                        'rincian' => $pp->rincian,
                                                        'total' => $total
                                                    ])
                                                    <div class="btn-export-group p-3 border-top bg-light">
                                                        <a href="{{ route('perjadin.export.sby', $pp->id) }}" 
                                                           class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-file-excel me-1"></i> SBY
                                                        </a>
                                                        <a href="{{ route('perjadin.export.kuitansi', $pp->id) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-receipt me-1"></i> Kuitansi
                                                        </a>
                                                        <a href="{{ route('perjadin.export.amplop', $pp->id) }}" 
                                                           class="btn btn-sm btn-outline-info">
                                                            <i class="fas fa-envelope me-1"></i> Amplop
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                {{-- NON PEGAWAI --}}
                                @if($sub->nonpegawai->count())
                                <div>
                                    <h6 class="fw-semibold text-success mb-3">
                                        <i class="fas fa-user-friends me-2"></i>Non Pegawai
                                    </h6>
                                    <div class="accordion accordion-custom" id="npAccordion{{ $kIndex }}{{ $sIndex }}">
                                        @foreach($sub->nonpegawai as $nIndex => $np)
                                        @php $total = $np->rincian->sum('total'); @endphp
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" 
                                                    type="button"
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#np{{ $kIndex }}{{ $sIndex }}{{ $nIndex }}">
                                                    <div class="d-flex justify-content-between w-100 me-3 align-items-center">
                                                        <span>
                                                            <i class="fas fa-user-circle me-2"></i>
                                                            {{ $np->nama }}
                                                            @if($np->instansi)
                                                            <small class="text-muted ms-2">
                                                                ({{ $np->instansi }})
                                                            </small>
                                                            @endif
                                                        </span>
                                                        <span class="text-success fw-semibold">
                                                            Rp{{ number_format($total, 0, ',', '.') }}
                                                        </span>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="np{{ $kIndex }}{{ $sIndex }}{{ $nIndex }}" 
                                                 class="accordion-collapse collapse" 
                                                 data-bs-parent="#npAccordion{{ $kIndex }}{{ $sIndex }}">
                                                <div class="accordion-body p-0">
                                                    @include('dashboard.perjadin.partials.table-rincian', [
                                                        'rincian' => $np->rincian,
                                                        'total' => $total
                                                    ])
                                                    <div class="btn-export-group p-3 border-top bg-light">
                                                        <a href="{{ route('perjadin.export.sbyNonPegawai', $np->id) }}" 
                                                           class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-file-excel me-1"></i> SBY
                                                        </a>
                                                        <a href="{{ route('perjadin.export.kuitansiNonPegawai', $np->id) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-receipt me-1"></i> Kuitansi
                                                        </a>
                                                        <a href="{{ route('perjadin.export.amplopNonPegawai', $np->id) }}" 
                                                           class="btn btn-sm btn-outline-info">
                                                            <i class="fas fa-envelope me-1"></i> Amplop
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Belum ada data peserta</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ================= TOTAL KESELURUHAN ================= --}}
    <div class="total-card p-4 mt-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h6 class="mb-1 text-white opacity-75">
                    <i class="fas fa-chart-line me-2"></i>Total Keseluruhan
                </h6>
                <p class="mb-0 small text-white-50">Seluruh biaya perjalanan dinas</p>
            </div>
            <div class="text-end">
                <div class="fw-bold text-white" style="font-size: 2rem;">
                    <i class="fas fa-rupiah-sign me-1"></i>
                    {{ number_format($grandTotalPerjalanan ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

</div>
@endsection