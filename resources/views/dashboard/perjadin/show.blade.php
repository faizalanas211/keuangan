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
    <ul class="nav nav-tabs mb-3" id="kelompokTab" role="tablist">
    @foreach($perjalanan->kelompokPerjalanan as $kIndex => $kelompok)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $kIndex == 0 ? 'active' : '' }}"
                id="tab-{{ $kIndex }}"
                data-bs-toggle="tab"
                data-bs-target="#content-{{ $kIndex }}"
                type="button">

                {{ strtoupper($kelompok->nama_kelompok) }}
            </button>
        </li>
    @endforeach
</ul>

   <div class="tab-content">

@foreach($perjalanan->kelompokPerjalanan as $kIndex => $kelompok)
<div class="tab-pane fade {{ $kIndex == 0 ? 'show active' : '' }}" 
     id="content-{{ $kIndex }}">

    <div class="card card-shadow mb-4">
        <div class="card-body">

            {{-- LOOP SUB KELOMPOK --}}
            @forelse($kelompok->subKelompok as $sIndex => $sub)

@php
    $totalST = 0;

    foreach ($sub->pegawai as $pp) {
        $totalST += $pp->rincian->sum('total');
    }
    foreach ($sub->nonpegawai as $np) {
        $totalST += $np->rincian->sum('total');
    }
@endphp

<div class="card card-shadow mb-4">
    <div class="card-body">

        {{-- HEADER ST --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="fw-bold mb-1">
                    No ST: {{ $sub->nomor_st ?? '-' }}
                </h6>
                <small class="text-muted">
                    {{ $sub->tanggal_st ? \Carbon\Carbon::parse($sub->tanggal_st)->format('d F Y') : '-' }}
                </small>
            </div>

            <span class="badge bg-success">
                Rp{{ number_format($totalST,0,',','.') }}
            </span>
        </div>

        {{-- ================= PEGAWAI ================= --}}
        @if($sub->pegawai->count())
        <div class="mb-4">
            <h6 class="fw-semibold text-primary mb-2">Pegawai</h6>

            <div class="accordion" id="pegawaiAccordion{{ $kIndex }}{{ $sIndex }}">
                @foreach($sub->pegawai as $pIndex => $pp)
                @php $total = $pp->rincian->sum('total'); @endphp

                <div class="accordion-item mb-2 border rounded">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed"
                            data-bs-toggle="collapse"
                            data-bs-target="#pegawai{{ $kIndex }}{{ $sIndex }}{{ $pIndex }}">

                            <div class="d-flex justify-content-between w-100 me-3">
                                <span>
                                    {{ $pp->pegawai->nama }}
                                    <small class="text-muted">
                                        ({{ $pp->pegawai->nip }})
                                    </small>
                                </span>

                                <span class="text-success fw-semibold">
                                    Rp{{ number_format($total,0,',','.') }}
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
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif


        {{-- ================= NON PEGAWAI ================= --}}
        @if($sub->nonpegawai->count())
        <div>
            <h6 class="fw-semibold text-success mb-2">Non Pegawai</h6>

            <div class="accordion" id="npAccordion{{ $kIndex }}{{ $sIndex }}">
                @foreach($sub->nonpegawai as $nIndex => $np)
                @php $total = $np->rincian->sum('total'); @endphp

                <div class="accordion-item mb-2 border rounded">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed"
                            data-bs-toggle="collapse"
                            data-bs-target="#np{{ $kIndex }}{{ $sIndex }}{{ $nIndex }}">

                            <div class="d-flex justify-content-between w-100 me-3">
                                <span>
                                    {{ $np->nama }}
                                    @if($np->instansi)
                                        <small class="text-muted">
                                            ({{ $np->instansi }})
                                        </small>
                                    @endif
                                </span>

                                <span class="text-success fw-semibold">
                                    Rp{{ number_format($total,0,',','.') }}
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
<p class="text-muted">Belum ada data</p>
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