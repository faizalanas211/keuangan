@extends('layouts.admin')

@section('breadcrumb')
    <li class="breadcrumb-item">Keuangan</li>
    <li class="breadcrumb-item active fw-semibold text-success">
        Penghasilan Pegawai
    </li>
@endsection

@section('content')

<style>
/* =========================
   GREEN PREMIUM – PENGHASILAN
   ========================= */
.card-green {
    border-radius: 18px;
    border: none;
    box-shadow: 0 18px 40px rgba(16, 185, 129, 0.12);
}

.btn-green {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    border: none;
    color: #fff;
    font-weight: 600;
    border-radius: 12px;
    padding: 10px 18px;
    box-shadow: 0 8px 20px rgba(22, 163, 74, .35);
}

.btn-green:hover {
    opacity: .9;
    color: #fff;
}

.btn-soft-green {
    background: #ecfdf5;
    color: #166534;
    border-radius: 12px;
    font-weight: 500;
}

.table thead th {
    font-size: 12px;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #166534;
    background: #ecfdf5;
    border-bottom: none;
}

.table tbody tr {
    transition: all .2s ease;
}

.table tbody tr:hover {
    background: #f0fdf4;
}

.total-green {
    color: #16a34a;
    font-weight: 700;
}

.filter-box input {
    border-radius: 12px;
}

.action-icon {
    color: #16a34a;
}

.action-icon:hover {
    color: #15803d;
}
</style>

<div class="card card-green p-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-success">
                Penghasilan Pegawai
            </h4>
            <small class="text-muted">
                Daftar penghasilan pegawai per periode
            </small>
        </div>
        <a href="{{ route('penghasilan.create') }}" class="btn btn-green">
            + Tambah Penghasilan
        </a>
    </div>

    {{-- FILTER BULAN --}}
    <form method="GET" class="row g-2 mb-4 filter-box">
        <div class="col-md-3">
            <input type="month"
                   name="bulan"
                   class="form-control"
                   value="{{ request('bulan') }}">
        </div>
        <div class="col-md-4">
            <button class="btn btn-green me-1">
                <i class="bx bx-filter-alt"></i> Filter
            </button>
            <a href="{{ route('penghasilan.index') }}"
               class="btn btn-soft-green">
                Reset
            </a>
        </div>
    </form>

    {{-- TABLE --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
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
                    <th class="text-end">Total</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($penghasilans as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>

                    {{-- PEGAWAI --}}
                    <td>
                        <div class="fw-semibold">
                            {{ $item->pegawai->nama ?? '-' }}
                        </div>
                        <small class="text-muted">
                            {{ $item->pegawai->nip ?? '' }}
                        </small>
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
                    <td class="text-end total-green">
                        {{ number_format($item->total_penghasilan, 0, ',', '.') }}
                    </td>

                    {{-- AKSI --}}
                    <td class="text-center">
                        <a href="{{ route('penghasilan.edit', $item->id) }}"
                           class="action-icon"
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
