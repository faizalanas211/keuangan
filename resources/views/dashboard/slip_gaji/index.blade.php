@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">Keuangan</li>
<li class="breadcrumb-item active fw-semibold text-success">
    Cetak Slip Gaji
</li>
@endsection

@section('content')

<style>
/* =========================
   GREEN PREMIUM – SLIP GAJI
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

.btn-outline-green {
    border: 1px solid #22c55e;
    color: #166534;
    border-radius: 10px;
    font-weight: 600;
}

.btn-outline-green:hover {
    background: #22c55e;
    color: #fff;
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

.filter-box select,
.filter-box input {
    border-radius: 12px;
}

.total-bersih {
    color: #15803d;
    font-weight: 700;
}
</style>

<div class="card card-green p-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-success">
                Slip Gaji Pegawai
            </h4>
            <small class="text-muted">
                Cetak slip gaji berdasarkan pegawai dan periode
            </small>
        </div>
    </div>

    {{-- FILTER --}}
    <form method="GET" class="row g-2 mb-4 filter-box">

        <div class="col-md-4">
            <select name="pegawai_id" class="form-select">
                <option value="">-- Pilih Pegawai --</option>

                @foreach ($pegawais as $p)
                    <option value="{{ $p->id }}"
                        {{ request('pegawai_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} - {{ $p->nip }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <input type="month"
                   name="bulan"
                   class="form-control"
                   value="{{ request('bulan') }}">
        </div>

        <div class="col-md-3">
            <button class="btn btn-green">
                <i class="bx bx-search"></i> Tampilkan
            </button>
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
                    <th class="text-end">Penghasilan Bersih</th>
                    <th>Terbilang</th>
                    <th class="text-center">Cetak</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($results as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>

                    <td>
                        <div class="fw-semibold">
                            {{ $row['pegawai']->nama }}
                        </div>
                        <small class="text-muted">
                            {{ $row['pegawai']->nip }}
                        </small>
                    </td>

                    <td>{{ $row['periode'] }}</td>

                    <td class="text-end total-bersih">
                        Rp {{ number_format($row['bersih'], 0, ',', '.') }}
                    </td>

                    <td class="text-muted">
                        {{ \App\Helpers\Terbilang::convert($row['bersih']) }} rupiah
                    </td>

                    <td class="text-center">
                        <a href="{{ route('slip-gaji.cetak', [$row['pegawai']->id, $row['bulan']]) }}"
                           class="btn btn-sm btn-outline-green"
                           title="Cetak Slip Gaji">
                            <i class="bx bx-printer"></i>
                        </a>
                    </td>
                </tr>

                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Pilih pegawai dan periode untuk menampilkan slip gaji.
                    </td>
                </tr>
                @endforelse
            </tbody>

        </table>
    </div>

</div>
@endsection
