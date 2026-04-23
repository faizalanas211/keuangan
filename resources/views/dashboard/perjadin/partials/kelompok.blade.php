<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body p-3">

        {{-- INFO KELOMPOK --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold small text-secondary">Nomor ST</label>
                <input type="text" name="kelompok[{{ $key }}][nomor_st]" class="form-control form-control-sm" placeholder="Masukkan Nomor ST">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold small text-secondary">Tanggal ST</label>
                <input type="date" name="kelompok[{{ $key }}][tanggal_st]" class="form-control form-control-sm">
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="d-flex flex-wrap gap-2 mb-3 pb-1 border-bottom align-items-center">
            @if($key == 'peserta')
                <button type="button" class="btn btn-outline-primary btn-sm px-3"
                    onclick="addPegawaiRow('{{ $key }}')">
                    <i class="bi bi-person-plus me-1"></i> + Pegawai
                </button>
                <button type="button" class="btn btn-outline-success btn-sm px-3"
                    onclick="addNonPegawaiRow('{{ $key }}')">
                    <i class="bi bi-person-badge-plus me-1"></i> + Non Pegawai
                </button>
            @elseif($tipe == 'pegawai')
                <button type="button" class="btn btn-outline-primary btn-sm px-3"
                    onclick="addPegawaiRow('{{ $key }}')">
                    <i class="bi bi-person-plus me-1"></i> + Pegawai
                </button>
            @else
                <button type="button" class="btn btn-outline-success btn-sm px-3"
                    onclick="addNonPegawaiRow('{{ $key }}')">
                    <i class="bi bi-person-badge-plus me-1"></i> + Non Pegawai
                </button>
            @endif

            {{-- Tombol Copy --}}
            <button type="button"
                id="btnCopy-{{ $key }}"
                onclick="copyToAllPeserta('{{ $key }}')"
                class="btn btn-outline-info btn-sm ms-auto"
                style="display:none;">
                <i class="bi bi-files me-1"></i> Copy ke Semua
            </button>
        </div>

        {{-- CONTAINER --}}
        <div id="container-{{ $key }}" class="mt-2">
            {{-- Dynamic rows will appear here --}}
        </div>

    </div>
</div>