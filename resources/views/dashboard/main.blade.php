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

    <div>
        <h4 class="fw-bold mb-1">Penghasilan Bulan Ini</h4>
        <p class="text-muted mb-0">
            Ringkasan gaji dan potongan periode {{ now()->translatedFormat('F Y') }}
        </p>
    </div>

   
    <div class="col-md-4">
        <div class="card shadow-sm rounded-4 h-100">
            <div class="card-body">
                <small class="text-muted">Penghasilan</small>
                <h4 class="fw-bold text-success">
                    Rp{{ number_format($totalPenghasilan,0,',','.') }}
                </h4>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm rounded-4 h-100">
            <div class="card-body">
                <small class="text-muted">Potongan</small>
                <h4 class="fw-bold text-danger">
                    Rp{{ number_format($totalPotongan,0,',','.') }}
                </h4>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm rounded-4 h-100">
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
  
    {{-- RIWAYAT PENGHASILAN --}}
    <div class="card mt-4 shadow-sm rounded-4">
        <div class="card-header bg-white border-0">
            <h5 class="fw-bold mb-0">Riwayat Penghasilan</h5>
            <small class="text-muted">Beberapa bulan terakhir</small>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Bulan</th>
                            <th>Penghasilan</th>
                            <th>Potongan</th>
                            <th>Gaji Bersih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayatGaji as $item)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($item->periode)->translatedFormat('F Y') }}</td>
                                <td class="text-success">
                                    Rp{{ number_format($item->total_penghasilan,0,',','.') }}
                                </td>
                                <td class="text-danger">
                                    Rp{{ number_format($item->total_potongan,0,',','.') }}
                                </td>
                                <td class="fw-semibold text-primary">
                                    Rp{{ number_format($item->gaji_bersih,0,',','.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Belum ada riwayat penghasilan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @endif

</div>
@endsection
