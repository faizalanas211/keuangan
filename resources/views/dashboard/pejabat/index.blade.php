@extends('layouts.admin')

@section('breadcrumb')
    <li class="breadcrumb-item active fw-semibold" style="color: #2d6a4f;">Data Pejabat Aktif Saat Ini</li>
@endsection

@section('content')

<style>
    /* ================= MAIN STYLING ================= */
    .card-pejabat {
        border: none;
        border-radius: 20px;
        transition: all 0.3s ease;
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    
    .card-pejabat:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
    }
    
    /* ================= BUTTONS ================= */
    .btn-green {
        background: linear-gradient(135deg, #2d6a4f 0%, #40916c 100%);
        border: none;
        color: white;
        font-weight: 600;
        border-radius: 12px;
        padding: 10px 20px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(45, 106, 79, 0.25);
    }
    
    .btn-green:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(45, 106, 79, 0.35);
        color: white;
    }
    
    .btn-outline-green {
        background: transparent;
        border: 1.5px solid #2d6a4f;
        color: #2d6a4f;
        border-radius: 12px;
        font-weight: 500;
        transition: all 0.2s;
        padding: 8px 20px;
    }
    
    .btn-outline-green:hover {
        background: linear-gradient(135deg, #2d6a4f 0%, #40916c 100%);
        color: white;
        transform: translateY(-2px);
    }
    
    /* ================= BADGE ================= */
    .badge-jenis {
        background: linear-gradient(135deg, #2d6a4f 0%, #40916c 100%);
        padding: 6px 14px;
        border-radius: 30px;
        font-weight: 500;
        font-size: 0.7rem;
        letter-spacing: 0.3px;
        display: inline-block;
    }
    
    .badge-status-aktif {
        background: #e8f5e9;
        color: #2d6a4f;
        padding: 5px 12px;
        border-radius: 30px;
        font-size: 0.7rem;
        font-weight: 500;
    }
    
    .badge-status-nonaktif {
        background: #f3f4f6;
        color: #6b7280;
        padding: 5px 12px;
        border-radius: 30px;
        font-size: 0.7rem;
        font-weight: 500;
    }
    
    /* ================= ACTION ICONS ================= */
    .action-icon {
        transition: all 0.2s ease;
        cursor: pointer;
        background: none;
        border: none;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .action-icon i {
        font-size: 1rem;
    }
    
    .action-icon.edit {
        color: #2d6a4f;
        background: #e8f5e9;
    }
    
    .action-icon.edit:hover {
        background: #2d6a4f;
        color: white;
        transform: scale(1.02);
    }
    
    .action-icon.delete {
        color: #dc2626;
        background: #fef2f2;
    }
    
    .action-icon.delete:hover {
        background: #dc2626;
        color: white;
        transform: scale(1.02);
    }
    
    /* ================= TYPOGRAPHY ================= */
    .nama-pejabat {
        color: #1f2937;
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 4px;
    }
    
    .jabatan-pejabat {
        color: #6b7280;
        font-size: 0.75rem;
        margin-bottom: 12px;
    }
    
    .periode-pejabat {
        color: #9ca3af;
        font-size: 0.7rem;
        margin-bottom: 12px;
    }
    
    /* ================= FOOTER CARD ================= */
    .card-footer-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 12px;
        margin-top: 8px;
        border-top: 1px solid #e5e7eb;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    /* ================= EMPTY STATE ================= */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 1rem;
    }
    
    .empty-state-text {
        color: #9ca3af;
        font-size: 0.9rem;
    }
    
    /* ================= TOAST ================= */
    .toast-notification {
        animation: slideInRight 0.3s ease;
        border-radius: 12px;
        font-weight: 500;
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    /* ================= RESPONSIVE ================= */
    @media (max-width: 768px) {
        .card-pejabat {
            margin-bottom: 1rem;
        }
        
        .btn-green {
            padding: 8px 16px;
            font-size: 0.85rem;
        }
        
        .card-footer-actions {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }
        
        .action-buttons {
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>

<div class="container-fluid px-0">
    {{-- HEADER SECTION --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1f2937;">
                <i class="fas fa-users me-2" style="color: #2d6a4f;"></i>Data Pejabat
            </h4>
            <p class="text-muted small mb-0">Kelola data pejabat untuk periode tertentu</p>
        </div>
        <div>
            <button type="button" class="btn btn-green" data-bs-toggle="modal" data-bs-target="#modalCreatePejabat">
                <i class="fas fa-plus me-2"></i>Tambah Pejabat
            </button>
        </div>
    </div>

    {{-- CARD GRID --}}
    <div class="row g-4">
        @forelse ($data as $item)
        <div class="col-md-6 col-lg-4">
            <div class="card-pejabat h-100">
                <div class="card-body p-4">
                    
                    {{-- HEADER CARD - Hanya badge jenis --}}
                    <div class="mb-3">
                        <span class="badge-jenis text-white">
                            <i class="fas fa-tag me-1"></i> {{ $item->jenisPejabat->nama }}
                        </span>
                    </div>
                    
                    {{-- BODY CARD - Informasi Pejabat --}}
                    <div class="mb-3">
                        <h5 class="nama-pejabat">
                            <i class="fas fa-user-circle me-2" style="color: #2d6a4f;"></i>{{ $item->pegawai->nama }}
                        </h5>
                        <div class="jabatan-pejabat">
                            <i class="fas fa-briefcase me-1"></i> {{ $item->pegawai->jabatan ?? '-' }}
                        </div>
                        <div class="periode-pejabat">
                            <i class="far fa-calendar-alt me-1"></i>
                            {{ \Carbon\Carbon::parse($item->periode_mulai)->translatedFormat('d M Y') }}
                            <i class="fas fa-arrow-right mx-1"></i>
                            {{ \Carbon\Carbon::parse($item->periode_selesai)->translatedFormat('d M Y') }}
                        </div>
                    </div>
                    
                    {{-- FOOTER CARD - Status dan Aksi --}}
                    <div class="card-footer-actions">
                        <div>
                            @if($item->is_active)
                                <span class="badge-status-aktif">
                                    <i class="fas fa-check-circle me-1"></i> Aktif
                                </span>
                            @else
                                <span class="badge-status-nonaktif">
                                    <i class="fas fa-clock me-1"></i> Nonaktif
                                </span>
                            @endif
                        </div>
                        
                        <div class="action-buttons">
                            <button type="button"
                                onclick="openEditModal(
                                    '{{ $item->id }}',
                                    '{{ $item->jenis_pejabat_id }}',
                                    '{{ $item->pegawai_id }}',
                                    '{{ \Carbon\Carbon::parse($item->periode_mulai)->format('Y-m-d') }}',
                                    '{{ \Carbon\Carbon::parse($item->periode_selesai)->format('Y-m-d') }}',
                                    '{{ $item->is_active }}'
                                )"
                                class="action-icon edit"
                                title="Edit Pejabat">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>

                            <form action="{{ route('pejabat.destroy', $item->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus pejabat ini?')"
                                  class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-icon delete" title="Hapus Pejabat">
                                    <i class="bi bi-trash3"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        @empty
        
        {{-- EMPTY STATE --}}
        <div class="col-12">
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-users-slash"></i>
                </div>
                <p class="empty-state-text">Belum ada data pejabat</p>
                <button type="button" class="btn btn-outline-green mt-3" data-bs-toggle="modal" data-bs-target="#modalCreatePejabat">
                    <i class="fas fa-plus me-2"></i>Tambah Pejabat Sekarang
                </button>
            </div>
        </div>
        @endforelse
    </div>
</div>

{{-- Include Modal Create --}}
@include('dashboard.pejabat.partials.modal-create', [
    'jenisPejabat' => $jenisPejabat ?? [],
    'pegawai' => $pegawai ?? []
])

{{-- Include Modal Edit --}}
@include('dashboard.pejabat.partials.modal-edit', [
    'jenisPejabat' => $jenisPejabat ?? [],
    'pegawai' => $pegawai ?? []
])

@endsection