<div class="table-responsive">
    <table class="table table-detail table-hover mb-0">
        <thead>
            <tr>
                <th style="width: 30%">Jenis Biaya</th>
                <th style="width: 15%">Volume</th>
                <th style="width: 15%">Satuan</th>
                <th style="width: 20%">Tarif</th>
                <th style="width: 20%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rincian as $r)
            <tr>
                <td>{{ $r->jenisBiaya->nama_biaya ?? '-' }}</td>
                <td>{{ (int) ($r->volume ?? 0) }}</td>
                <td>{{ $r->satuan ?? '-' }}</td>
                <td>Rp{{ number_format($r->tarif ?? 0, 0, ',', '.') }}</td>
                <td><strong class="text-success">Rp{{ number_format($r->total ?? 0, 0, ',', '.') }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="fas fa-receipt me-2"></i>Belum ada rincian biaya
                </td>
            </tr>
            @endforelse
            <tr class="table-active fw-semibold">
                <td colspan="4" class="text-end">Subtotal</td>
                <td class="text-success">Rp{{ number_format($total ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</div>