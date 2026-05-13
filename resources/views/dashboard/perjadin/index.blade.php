@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('dashboard') }}">Dashboard</a>
</li>
<li class="breadcrumb-item active text-success fw-semibold">
    Data Perjalanan Dinas
</li>
@endsection

@section('content')

<style>
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
.card-shadow{
    border:none;
    border-radius:14px;
    box-shadow:0 8px 20px rgba(0,0,0,0.05);
}
</style>

<div class="card card-shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-success fw-semibold">
            Data Perjalanan Dinas
        </h5>
        <a href="{{ route('perjadin.create') }}" class="btn btn-green">
            + Tambah Perjalanan Dinas
        </a>
    </div>

    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tingkat</th>
                        <th>Rute</th>
                        <th>Tanggal</th>
                        <th>Kegiatan</th>
                        <th>Peserta</th>
                        @if(auth()->user()->role == 'admin')
                        <th>Dibuat Oleh</th>
                        @endif
                        <th width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($perjalanans as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->tingkat_perjalanan }}</td>
                        <td>
                            {{ $item->dari_kota }} ke {{ $item->tujuan_kota }}
                        </td>
                        <td>
                            {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') }}
                            <br>
                            <small class="text-muted">
                                s/d {{ \Carbon\Carbon::parse($item->tanggal_akhir)->format('d M Y') }}
                            </small>
                        </td>
                        <td>
                            {{ Str::limit($item->nama_kegiatan, 60) }}
                        </td>
                        <td>
                            <span class="badge bg-success">
                                {{ $item->pegawai->count() + $item->nonpegawai->count() }} Orang
                            </span>
                        </td>
                        @if(auth()->user()->role == 'admin')
                        <td>
                            @php
                                $createdBy = \App\Models\User::find($item->created_by);
                            @endphp
                            {{ $createdBy ? $createdBy->name : '-' }}
                        </td>
                        @endif
                        <td>
                            <div class="d-flex gap-4">
                                {{-- Detail --}}
                                <a href="{{ route('perjadin.show',$item->id) }}"
                                class="text-primary"
                                title="Detail">
                                    <i class="bi bi-eye fs-5"></i>
                                </a>

                                {{-- Edit --}}
                                <a href="{{ route('perjadin.edit',$item->id) }}"
                                class="text-warning"
                                title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </a>

                                {{-- Hapus --}}
                                <form action="{{ route('perjadin.destroy',$item->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Yakin hapus data?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn p-0 border-0 bg-transparent text-danger"
                                            title="Hapus">
                                        <i class="bi bi-trash fs-5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role == 'admin' ? 8 : 7 }}" class="text-center text-muted">
                            Belum ada data perjalanan dinas
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $perjalanans->links() }}
        </div>

    </div>
</div>

@endsection