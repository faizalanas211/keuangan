@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">Keuangan</li>
<li class="breadcrumb-item active text-primary fw-semibold">
    Cetak Slip Gaji
</li>
@endsection

@section('content')
<div class="card p-3">

    {{-- FILTER --}}
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <select name="pegawai_id" class="form-select">
                <option value="">-- Pilih Pegawai --</option>

                {{-- ✅ FIX: pakai $pegawais --}}
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
            <button class="btn btn-primary">
                <i class="bx bx-search"></i> Tampilkan
            </button>
        </div>
    </form>

    {{-- TABEL --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
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
                        <strong>{{ $row['pegawai']->nama }}</strong><br>
                        <small class="text-muted">
                            {{ $row['pegawai']->nip }}
                        </small>
                    </td>

                    <td>{{ $row['periode'] }}</td>

                    <td class="text-end fw-semibold">
                        Rp {{ number_format($row['bersih'], 0, ',', '.') }}
                    </td>

                    <td class="text-muted">
                        {{ \App\Helpers\Terbilang::convert($row['bersih']) }} rupiah
                    </td>

                    <td class="text-center">
                        <a href="{{ route('slip-gaji.cetak', [$row['pegawai']->id, $row['bulan']]) }}"
                           class="btn btn-sm btn-outline-primary"
                           title="Cetak Slip">
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
