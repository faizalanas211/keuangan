@php
    $data = $perjalanan ?? null;
@endphp

<div class="card card-shadow mb-4">
    <div class="card-body">
        <h5 class="section-title mb-3">Informasi Perjalanan</h5>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Tingkat Perjalanan</label>
                <input type="text" name="tingkat_perjalanan" class="form-control"
                    value="{{ old('tingkat_perjalanan', $data->tingkat_perjalanan ?? '') }}">
            </div>
            <div class="col-md-6">
                <label>Alat Angkutan <span class="text-danger">*</span></label>
                <input type="text" name="alat_angkutan" class="form-control" required
                    value="{{ old('alat_angkutan', $data->alat_angkutan ?? '') }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Dari Kota <span class="text-danger">*</span></label>
                <input type="text" name="dari_kota" class="form-control" required
                    value="{{ old('dari_kota', $data->dari_kota ?? '') }}">
            </div>
            <div class="col-md-6">
                <label>Tujuan Kota <span class="text-danger">*</span></label>
                <input type="text" name="tujuan_kota" class="form-control" required
                    value="{{ old('tujuan_kota', $data->tujuan_kota ?? '') }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control" required
                    value="{{ old('tanggal_mulai', isset($data) ? optional($data->tanggal_mulai)->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-6">
                <label>Tanggal Akhir <span class="text-danger">*</span></label>
                <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control" required
                    value="{{ old('tanggal_akhir', isset($data) ? optional($data->tanggal_akhir)->format('Y-m-d') : '') }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Tanggal Terima <span class="text-danger">*</span></label>
                <input type="date" id="tanggal_terima" name="tanggal_terima" class="form-control" required
                    value="{{ old('tanggal_terima', isset($data) ? optional($data->tanggal_terima)->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-6">
                <label>Kode MAK</label>
                <input type="text" name="kode_mak" class="form-control"
                    value="{{ old('kode_mak', $data->kode_mak ?? '') }}">
            </div>
        </div>

        <div class="mb-3">
            <label>Output: Akun: Biaya Kegiatan dalam rangka komponen/subkomponen</label>
            <textarea name="akun_biaya" class="form-control" rows="3">{{ old('akun_biaya', $data->akun_biaya ?? '') }}</textarea>
        </div>

        <div class="mb-3">
            <label>Nama Kegiatan <span class="text-danger">*</span></label>
            <textarea name="nama_kegiatan" class="form-control" rows="3" required>{{ old('nama_kegiatan', $data->nama_kegiatan ?? '') }}</textarea>
        </div>
    </div>
</div>