@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item active text-primary fw-semibold">
    Dashboard Keuangan
</li>
@endsection

@section('content')
<div class="row g-4">

    {{-- ADMIN --}}
    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'Admin')

    <div class="col-md-3">
        <div class="card shadow-sm rounded-4">
            <div class="card-body">
                <small class="text-muted">Total Pegawai</small>
                <h3 class="fw-bold">{{ $totalPegawai }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm rounded-4">
            <div class="card-body">
                <small class="text-muted">Penghasilan Bulan Ini</small>
                <h5 class="fw-bold text-success">
                    Rp{{ number_format($totalPenghasilan,0,',','.') }}
                </h5>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm rounded-4">
            <div class="card-body">
                <small class="text-muted">Potongan Bulan Ini</small>
                <h5 class="fw-bold text-danger">
                    Rp{{ number_format($totalPotongan,0,',','.') }}
                </h5>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm rounded-4">
            <div class="card-body">
                <small class="text-muted">Gaji Bersih</small>
                <h5 class="fw-bold text-primary">
                    Rp{{ number_format($totalBersih,0,',','.') }}
                </h5>
            </div>
        </div>
    </div>

    {{-- PEGAWAI --}}
    @else

    <div class="col-md-4">
        <div class="card shadow-sm rounded-4">
            <div class="card-body">
                <small class="text-muted">Penghasilan</small>
                <h4 class="fw-bold text-success">
                    Rp{{ number_format($totalPenghasilan,0,',','.') }}
                </h4>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm rounded-4">
            <div class="card-body">
                <small class="text-muted">Potongan</small>
                <h4 class="fw-bold text-danger">
                    Rp{{ number_format($totalPotongan,0,',','.') }}
                </h4>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm rounded-4">
            <div class="card-body">
                <small class="text-muted">Gaji Bersih</small>
                <h4 class="fw-bold text-primary">
                    Rp{{ number_format($totalBersih,0,',','.') }}
                </h4>

                <a href="{{ route('slip-gaji.index') }}"
                   class="btn btn-sm btn-outline-primary mt-2">
                    Cetak Slip Gaji
                </a>
            </div>
        </div>
    </div>

    @endif

</div>
@endsection
