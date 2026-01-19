@extends('layouts.admin')

@section('breadcrumb')
    <li class="breadcrumb-item">Keuangan</li>
    <li class="breadcrumb-item active text-primary fw-semibold">
        Potongan Pegawai
    </li>
@endsection

@section('content')
<div class="card p-3">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0 fw-bold">Potongan Pegawai</h5>
            <small class="text-muted">Daftar potongan pegawai per periode</small>
        </div>
        <a href="{{ route('potongan.create') }}" class="btn btn-primary">
            + Tambah Potongan
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
            <a href="{{ route('potongan.index') }}"
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

                    <th class="text-end fw-semibold">Total</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($potongans as $item)
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
                    <td class="text-end fw-semibold text-danger">
                        {{ number_format($item->total_potongan, 0, ',', '.') }}
                    </td>

                    {{-- AKSI --}}
                    <td class="text-center">
                        <a href="{{ route('potongan.edit', $item->id) }}"
                           class="text-primary me-2"
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
