@extends('layouts.admin')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('dashboard') }}">Dashboard</a>
</li>
<li class="breadcrumb-item active text-success fw-semibold">
    Template Dokumen
</li>
@endsection

@section('content')

<div class="card card-shadow">
    <div class="card-header d-flex justify-content-between">
        <h5 class="text-success fw-semibold mb-0">Template Dokumen</h5>

        <a href="{{ route('template.create') }}" class="btn btn-success">
            + Upload Template
        </a>
    </div>

    <div class="card-body">

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Jenis</th>
                    <th>File</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $t)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $t->nama }}</td>
                    <td>
                        <span class="badge bg-info text-dark">
                            {{ str_replace('_', ' ', ucwords($t->jenis)) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ asset('storage/'.$t->file_path) }}" target="_blank">
                            Download
                        </a>
                    </td>
                    <td>
                        <div class="d-flex gap-2">

                            <a href="{{ route('template.edit',$t->id) }}"
                               class="text-warning">
                                <i class="bx bx-edit"></i>
                            </a>

                            <form action="{{ route('template.destroy',$t->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Hapus template?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn p-0 text-danger">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Belum ada template
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</div>

@endsection