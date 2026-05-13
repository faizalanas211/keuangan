<div class="table-responsive">
    <table class="table table-detail table-hover mb-0">
        <thead>
            <tr>
                <th style="width: 25%">Jenis Biaya</th>
                <th style="width: 10%">Volume</th>
                <th style="width: 10%">Satuan</th>
                <th style="width: 15%">Tarif</th>
                <th style="width: 15%">Total</th>
                <th style="width: 15%">Masuk Amplop?</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rincian as $r)
            <tr>
                <td>
                    {{ $r->jenisBiaya->nama_biaya }}
                    @if($r->uraian)
                        <br><small class="text-muted">{{ $r->uraian }}</small>
                    @endif
                </td>
                <td>{{ (int) ($r->volume ?? 0) }}</td>
                <td>{{ $r->satuan ?? '-' }}</td>
                <td>Rp{{ number_format($r->tarif ?? 0, 0, ',', '.') }}</td>
                <td><strong class="text-success">Rp{{ number_format($r->total ?? 0, 0, ',', '.') }}</strong></td>
                <td class="text-center">
                    @if($r->masuk_amplop)
                        <span style="color: #16a34a;">✓ Ya</span>
                    @else
                        <span style="color: #9ca3af;">✗ Tidak</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="fas fa-receipt me-2"></i>Belum ada rincian biaya
                </td>
            </tr>
            @endforelse
            <tr class="table-active fw-semibold">
                <td colspan="5" class="text-end">Subtotal</td>
                <td class="text-success">Rp{{ number_format($total ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</div>