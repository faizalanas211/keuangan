@extends('layouts.admin')

@section('breadcrumb')
    <li class="breadcrumb-item">Keuangan</li>
    <li class="breadcrumb-item active text-primary fw-semibold">
        Penghasilan Pegawai
    </li>
@endsection

@section('content')
<div class="card p-3">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0 fw-bold">Penghasilan Pegawai</h5>
            <small class="text-muted">Daftar penghasilan pegawai</small>
        </div>
        <a href="{{ route('penghasilan.create') }}" class="btn btn-primary">
            + Tambah Penghasilan
        </a>
    </div>

    {{-- FILTER BULAN --}}
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <input type="month"
                   name="bulan"
                   class="form-control"
                   value="{{ request('bulan') }}">
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary">
                <i class="bx bx-filter-alt"></i> Filter
            </button>
            <a href="{{ route('penghasilan.index') }}"
               class="btn btn-light ms-1">
                Reset
            </a>
        </div>
    </form>

    {{-- TABLE --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Pegawai</th>
                    <th>Periode</th>
                    <th class="text-end">Gaji Induk</th>
                    <th class="text-end">Tunj. Suami/Istri</th>
                    <th class="text-end">Tunj. Anak</th>
                    <th class="text-end">Tunj. Umum</th>
                    <th class="text-end">Tunj. Struktural</th>
                    <th class="text-end">Tunj. Fungsional</th>
                    <th class="text-end">Tunj. Beras</th>
                    <th class="text-end">Tunj. Pajak</th>
                    <th class="text-end">Pembulatan</th>
                    <th class="text-end fw-semibold">Total</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($penghasilans as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>

                    {{-- PEGAWAI --}}
                    <td>
                        <div class="fw-semibold">{{ $item->pegawai->nama ?? '-' }}</div>
                        <small class="text-muted">{{ $item->pegawai->nip ?? '' }}</small>
                    </td>

                    {{-- PERIODE --}}
                    <td>
                        @if ($item->tanggal)
                            {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('F Y') }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    {{-- NOMINAL --}}
                    <td class="text-end">{{ number_format($item->gaji_induk, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->tunj_suami_istri, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->tunj_anak, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->tunj_umum, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->tunj_struktural, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->tunj_fungsional, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->tunj_beras, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->tunj_pajak, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->pembulatan, 0, ',', '.') }}</td>

                    {{-- TOTAL --}}
                    <td class="text-end fw-semibold text-success">
                        {{ number_format($item->total_penghasilan, 0, ',', '.') }}
                    </td>

                    {{-- AKSI --}}
                    <td class="text-center">
                        <a href="{{ route('penghasilan.edit', $item->id) }}"
                           class="text-primary me-2"
                           title="Edit">
                            <i class="bx bx-edit bx-sm"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="14" class="text-center text-muted py-4">
                        Belum ada data penghasilan pegawai.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
