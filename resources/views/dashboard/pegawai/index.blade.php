@extends('layouts.admin')

@section('breadcrumb')
    <li class="breadcrumb-item active text-primary fw-semibold">Data Pegawai</li>
@endsection

@section('content')
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
            <thead class="table-light">
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
                        {{-- Nomor --}}
                        <td>{{ $pegawais->firstItem() + $loop->index }}</td>

                        {{-- Nama --}}
                        <td>
                            <strong>{{ ucwords($item->nama) }}</strong>
                        </td>

                        {{-- NIP --}}
                        <td>{{ $item->nip }}</td>

                        {{-- Jabatan --}}
                        <td>{{ $item->jabatan }}</td>

                        {{-- Pangkat / Golongan --}}
                        <td>{{ $item->pangkat_golongan }}</td>

                        {{-- Aksi --}}
                        <td class="text-center">
                            <div class="d-flex justify-content-center align-items-center">
                                <a href="{{ route('pegawai.edit', [$item->id, 'page' => request()->input('page', 1)]) }}"
                                   class="text-primary me-3" title="Edit">
                                    <i class="bx bx-edit bx-sm"></i>
                                </a>
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
                <span class="fw-medium">Menampilkan</span>
                <span class="fw-medium">{{ $pegawais->firstItem() ?? 0 }}</span>
                <span class="fw-medium">sampai</span>
                <span class="fw-medium">{{ $pegawais->lastItem() ?? 0 }}</span>
                <span class="fw-medium">dari</span>
                <span class="fw-medium">{{ $pegawais->total() }}</span>
                <span class="fw-medium">data pegawai</span>
            </div>

            <nav>
                <ul class="pagination mb-0">
                    {{-- First --}}
                    <li class="page-item {{ $pegawais->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $pegawais->url(1) }}">
                            <i class="bx bx-chevrons-left"></i>
                        </a>
                    </li>

                    {{-- Prev --}}
                    <li class="page-item {{ $pegawais->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $pegawais->previousPageUrl() }}">
                            <i class="bx bx-chevron-left"></i>
                        </a>
                    </li>

                    {{-- Numbers --}}
                    @foreach($pegawais->getUrlRange(
                        max(1, $pegawais->currentPage() - 2),
                        min($pegawais->lastPage(), $pegawais->currentPage() + 2)
                    ) as $page => $url)
                        <li class="page-item {{ $page == $pegawais->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach

                    {{-- Next --}}
                    <li class="page-item {{ !$pegawais->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $pegawais->nextPageUrl() }}">
                            <i class="bx bx-chevron-right"></i>
                        </a>
                    </li>

                    {{-- Last --}}
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
