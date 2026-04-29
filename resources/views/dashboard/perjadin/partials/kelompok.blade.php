<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body p-3">

        {{-- ACTION BUTTONS --}}
        <div class="d-flex flex-wrap gap-2 mb-3 pb-2 border-bottom align-items-center">

            {{-- Tambah ST --}}
            <button type="button"
                class="btn btn-outline-success btn-sm px-3"
                onclick="addSubKelompok('{{ $key }}')">
                <i class="bi bi-plus-circle me-1"></i> + ST
            </button>

            {{-- Tombol Copy --}}
            <button type="button"
                id="btnCopy-{{ $key }}"
                onclick="copyToAllPeserta('{{ $key }}')"
                class="btn btn-outline-info btn-sm ms-auto"
                style="display:none;">
                <i class="bi bi-files me-1"></i> Copy ke Semua
            </button>
        </div>

        {{-- CONTAINER SUB KELOMPOK --}}
        <div id="container-{{ $key }}">

            {{-- DEFAULT ST (index 0) --}}
            <div class="card border mb-3">
                <div class="card-body">

                    {{-- ST --}}
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label class="small text-secondary fw-semibold">Nomor ST</label>
                            <input type="text"
                                name="kelompok[{{ $key }}][0][nomor_st]"
                                class="form-control form-control-sm">
                        </div>

                        <div class="col-md-5">
                            <label class="small text-secondary fw-semibold">Tanggal ST</label>
                            <input type="date"
                                name="kelompok[{{ $key }}][0][tanggal_st]"
                                class="form-control form-control-sm">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button"
                                class="btn btn-outline-danger btn-sm w-100"
                                onclick="this.closest('.card').remove()">
                                Hapus
                            </button>
                        </div>
                    </div>

                    {{-- BUTTON PESERTA --}}
                    <div class="d-flex gap-2 mb-3">
                        @if($key == 'peserta')
                            <button type="button"
                                class="btn btn-outline-primary btn-sm"
                                onclick="addPegawaiRow('{{ $key }}', 0)">
                                + Pegawai
                            </button>

                            <button type="button"
                                class="btn btn-outline-success btn-sm"
                                onclick="addNonPegawaiRow('{{ $key }}', 0)">
                                + Non Pegawai
                            </button>
                        @elseif($tipe == 'pegawai')
                            <button type="button"
                                class="btn btn-outline-primary btn-sm"
                                onclick="addPegawaiRow('{{ $key }}', 0)">
                                + Pegawai
                            </button>
                        @else
                            <button type="button"
                                class="btn btn-outline-success btn-sm"
                                onclick="addNonPegawaiRow('{{ $key }}', 0)">
                                + Non Pegawai
                            </button>
                        @endif
                    </div>

                    {{-- CONTAINER PESERTA --}}
                    <div id="subkelompok-{{ $key }}-0"></div>

                </div>
            </div>

        </div>

    </div>
</div>