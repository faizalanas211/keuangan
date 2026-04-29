<div class="mb-3">

    {{-- INFO KELOMPOK --}}
    <div class="row g-3 mb-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label fw-semibold small text-secondary">Nomor ST</label>
            <input type="text" 
                   name="kelompok[{{ $key }}][nomor_st]" 
                   class="form-control form-control-sm" 
                   value="{{ $kelompokData['nomor_st'] ?? '' }}"
                   placeholder="Masukkan Nomor ST">
        </div>
        <div class="col-md-5">
            <label class="form-label fw-semibold small text-secondary">Tanggal ST</label>
            <input type="date"
                name="kelompok[{{ $key }}][tanggal_st]"
                class="form-control form-control-sm"
                value="{{ old('kelompok.'.$key.'.tanggal_st', 
                    !empty($kelompokData['tanggal_st']) 
                        ? \Carbon\Carbon::parse($kelompokData['tanggal_st'])->format('Y-m-d') 
                        : ''
                ) }}">
        </div>
        <div class="col-md-2">
            <button type="button"
                id="btnCopy-{{ $key }}"
                onclick="copyToAllPeserta('{{ $key }}')"
                class="btn btn-outline-info btn-sm w-100"
                style="display:none;">
                <i class="bi bi-files me-1"></i> Copy ke Semua
            </button>
        </div>
    </div>

    {{-- ACTION BUTTONS --}}
    <div class="d-flex flex-wrap gap-2 mb-3 pb-2 border-bottom">
        @if($key == 'peserta')
            <button type="button" class="btn btn-outline-primary btn-sm px-3" onclick="addPegawaiRow('{{ $key }}')">
                <i class="bi bi-person-plus me-1"></i> + Pegawai
            </button>
            <button type="button" class="btn btn-outline-success btn-sm px-3" onclick="addNonPegawaiRow('{{ $key }}')">
                <i class="bi bi-person-badge-plus me-1"></i> + Non Pegawai
            </button>
        @elseif($tipe == 'pegawai')
            <button type="button" class="btn btn-outline-primary btn-sm px-3" onclick="addPegawaiRow('{{ $key }}')">
                <i class="bi bi-person-plus me-1"></i> + Pegawai
            </button>
        @else
            <button type="button" class="btn btn-outline-success btn-sm px-3" onclick="addNonPegawaiRow('{{ $key }}')">
                <i class="bi bi-person-badge-plus me-1"></i> + Non Pegawai
            </button>
        @endif
    </div>

    {{-- CONTAINER UNTUK PESERTA --}}
    <div id="container-{{ $key }}"></div>

</div>