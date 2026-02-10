@extends('layouts.admin')

@section('breadcrumb')
    <li class="breadcrumb-item active text-success fw-semibold">Data Pegawai</li>
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
        <h5 class="card-title mb-3">Data Pegawai</h5>
        <div>
            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalGenerateAkun">
                <i class="bx bx-user-plus"></i> Generate Akun
            </button>
            <a href="{{ route('pegawai.create') }}" class="btn btn-primary">+ Tambah Data</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-success">
                <tr>
                    <th>#</th>
                    <th>Nama Pegawai</th>
                    <th>NIP</th>
                    <th>Jabatan</th>
                    <th>Pangkat, Golongan</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($pegawais as $item)
                    <tr>
                        <td>{{ $pegawais->firstItem() + $loop->index }}</td>

                        <td>
                            <strong class="text-success">
                                {{ ucwords($item->nama) }}
                            </strong>
                        </td>

                        <td>{{ $item->nip }}</td>

                        <td>{{ $item->jabatan }}</td>

                        <td>{{ $item->pangkat_golongan }}</td>

                        <td class="text-center">
                            <div class="d-flex justify-content-center align-items-center">

                                {{-- EDIT --}}
                                <a href="{{ route('pegawai.edit', [$item->id, 'page' => request()->input('page', 1)]) }}"
                                   class="me-3 action-icon edit"
                                   title="Edit">
                                    <i class="bx bx-edit bx-sm"></i>
                                </a>

                                {{-- DELETE --}}
                                <form action="{{ route('pegawai.destroy', $item->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus data pegawai ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link p-0 action-icon delete" title="Hapus">
                                        <i class="bx bx-trash bx-sm"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Tidak ada data pegawai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($pegawais->hasPages())
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 pt-3 border-top">
            <div class="mb-3 mb-md-0 text-muted">
                Menampilkan {{ $pegawais->firstItem() ?? 0 }}
                sampai {{ $pegawais->lastItem() ?? 0 }}
                dari {{ $pegawais->total() }} data pegawai
            </div>

            <nav>
                <ul class="pagination mb-0">
                    <li class="page-item {{ $pegawais->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $pegawais->url(1) }}">
                            <i class="bx bx-chevrons-left"></i>
                        </a>
                    </li>

                    <li class="page-item {{ $pegawais->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $pegawais->previousPageUrl() }}">
                            <i class="bx bx-chevron-left"></i>
                        </a>
                    </li>

                    @foreach($pegawais->getUrlRange(
                        max(1, $pegawais->currentPage() - 2),
                        min($pegawais->lastPage(), $pegawais->currentPage() + 2)
                    ) as $page => $url)
                        <li class="page-item {{ $page == $pegawais->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach

                    <li class="page-item {{ !$pegawais->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $pegawais->nextPageUrl() }}">
                            <i class="bx bx-chevron-right"></i>
                        </a>
                    </li>

                    <li class="page-item {{ !$pegawais->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $pegawais->url($pegawais->lastPage()) }}">
                            <i class="bx bx-chevrons-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
</div>
<div class="modal fade" id="modalGenerateAkun" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form action="{{ route('pegawai.generateAkun') }}" method="POST">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Akun Pegawai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    {{-- Generate Semua --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <strong>Pilih Pegawai</strong>

                        <button type="submit"
                                formaction="{{ route('pegawai.generateAkun') }}"
                                class="btn btn-sm btn-outline-success"
                                onclick="return confirm('Generate akun untuk semua pegawai yang belum punya akun?')">
                            Generate Semua
                        </button>
                    </div>

                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="checkAll">
                                </th>
                                <th>Nama</th>
                                <th>NIP</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pegawais as $pegawai)
                                <tr>
                                    @if($pegawai->user)
                                        <td></td>
                                        <td>{{ $pegawai->nama }}</td>
                                        <td>{{ $pegawai->nip }}</td>
                                        <td>
                                            <span class="badge bg-success">Sudah Ada Akun</span>
                                        </td>
                                    @else
                                        <td>
                                            <input type="checkbox"
                                                   name="pegawai_ids[]"
                                                   value="{{ $pegawai->id }}"
                                                   class="checkItem">
                                        </td>
                                        <td>{{ $pegawai->nama }}</td>
                                        <td>{{ $pegawai->nip }}</td>
                                        <td>
                                            <span class="badge bg-danger">Belum Ada Akun</span>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <small class="text-muted">
                        * Username = NIP, Password awal = NIP (wajib ganti password saat login pertama)
                    </small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Generate Akun Terpilih
                    </button>
                </div>
            </div>
        </form>
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
