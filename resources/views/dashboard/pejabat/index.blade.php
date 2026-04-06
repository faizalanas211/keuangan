@extends('layouts.admin')

@section('breadcrumb')
    <li class="breadcrumb-item active text-success fw-semibold">Data Pejabat Aktif Saat Ini</li>
@endsection

@section('content')

<style>
.btn-success-soft{
    background:linear-gradient(135deg,#22c55e,#16a34a);
    border:none;
    color:#fff;
    font-weight:600;
    box-shadow:0 4px 14px rgba(34,197,94,.35);
}

.btn-success-soft:hover{
    background:linear-gradient(135deg,#16a34a,#15803d);
}

.table-hover tbody tr:hover{
    background:#f0fdf4;
}

.action-icon{
    transition:.2s;
}

.action-icon.edit{color:#16a34a;}
.action-icon.edit:hover{color:#15803d;transform:scale(1.1);}

.action-icon.delete{color:#ef4444;}
.action-icon.delete:hover{color:#dc2626;transform:scale(1.1);}
</style>

<div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="card-title mb-3">Data Pejabat</h5>
        <div>
            <a href="{{ route('pejabat.create') }}" class="btn btn-primary">+ Tambah Data</a>
        </div>
    </div>

    <div class="row">

@forelse ($data as $item)
<div class="col-md-6 col-lg-4 mb-4">

    <div class="card shadow-sm border-0 h-100">
        <div class="card-body">

            {{-- JENIS --}}
            <span class="badge bg-success mb-2">
                {{ $item->jenisPejabat->nama }}
            </span>

            {{-- NAMA --}}
            <h5 class="fw-semibold text-success mb-1">
                {{ $item->pegawai->nama }}
            </h5>

            <div class="text-muted small mb-2">
                {{ $item->pegawai->jabatan }}
            </div>

            {{-- PERIODE --}}
            <div class="small text-muted mb-3">
                {{ \Carbon\Carbon::parse($item->periode_mulai)->format('d M Y') }}
                - 
                {{ \Carbon\Carbon::parse($item->periode_selesai)->format('d M Y') }}
            </div>

            {{-- AKSI --}}
            <div class="d-flex justify-content-end">
                <a href="{{ route('pejabat.edit', $item->id) }}"
                   class="me-3 action-icon edit">
                    <i class="bx bx-edit"></i>
                </a>

                <form action="{{ route('pejabat.destroy', $item->id) }}"
                      method="POST"
                      onsubmit="return confirm('Yakin hapus?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-link p-0 action-icon delete">
                        <i class="bx bx-trash"></i>
                    </button>
                </form>
            </div>

        </div>
    </div>

</div>
@empty
<div class="col-12 text-center text-muted py-5">
    Tidak ada pejabat aktif saat ini.
</div>
@endforelse

</div>
</div>

<script>
document.getElementById('checkAll')?.addEventListener('change', function () {
    document.querySelectorAll('.checkItem').forEach(cb => {
        cb.checked = this.checked;
    });
});
</script>

@endsection
