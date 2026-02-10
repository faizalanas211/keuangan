@extends('layouts.admin')

@section('breadcrumb')
    <li class="breadcrumb-item">Keuangan</li>
    <li class="breadcrumb-item active fw-semibold text-success">
        Potongan Pegawai
    </li>
@endsection

@section('content')

<style>
/* =========================
   GREEN PREMIUM – POTONGAN
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

/* Total potongan – tetap tegas tapi rapi */
.total-potongan {
    color: #15803d;
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
                Potongan Pegawai
            </h4>
            <small class="text-muted">
                Daftar potongan pegawai per periode
            </small>
        </div>
        <a href="{{ route('potongan.create') }}" class="btn btn-green">
            + Tambah Potongan
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
            <a href="{{ route('potongan.index') }}"
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

                    <th class="text-end">Pot. Wajib</th>
                    <th class="text-end">Pot. Pajak</th>
                    <th class="text-end">BPJS</th>
                    <th class="text-end">BPJS Lain</th>
                    <th class="text-end">Dana Sosial</th>
                    <th class="text-end">Bank Jateng</th>
                    <th class="text-end">Bank BJB</th>
                    <th class="text-end">Parcel</th>
                    <th class="text-end">Kop. Sayuk Rukun</th>
                    <th class="text-end">Kop. Mitra Lingua</th>

                    <th class="text-end">Total</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($potongans as $item)
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

                    {{-- POTONGAN --}}
                    <td class="text-end">{{ number_format($item->potongan_wajib, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->potongan_pajak, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->potongan_bpjs, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->potongan_bpjs_lain, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->dana_sosial, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->bank_jateng, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->bank_bjb, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->parcel, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->kop_sayuk_rukun, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->kop_mitra_lingua, 0, ',', '.') }}</td>

                    {{-- TOTAL --}}
                    <td class="text-end total-potongan">
                        {{ number_format($item->total_potongan, 0, ',', '.') }}
                    </td>

                    {{-- AKSI --}}
                    <td class="text-center">
                        <a href="{{ route('potongan.edit', $item->id) }}"
                           class="action-icon"
                           title="Edit">
                            <i class="bx bx-edit bx-sm"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="15" class="text-center text-muted py-4">
                        Belum ada data potongan pegawai.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
