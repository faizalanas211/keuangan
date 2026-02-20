<table border="1">
    <thead>
        <tr>
            <th colspan="10" align="center">
                DAFTAR NOMINATIF PEGAWAI
            </th>
        </tr>

        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Dari</th>
            <th>Ke</th>
            <th>Tanggal</th>
            <th>Hari</th>
            <th>Tarif</th>
            <th>Jumlah</th>
            <th>Jumlah Dibayar</th>
            <th>Ket</th>
        </tr>
    </thead>

    <tbody>
    @php $no=1; $grand=0; @endphp

    @foreach($perjalanan->pegawaiPerjalanan as $pp)

        @php
            $totalPegawai = $pp->rincian->sum('total');
            $grand += $totalPegawai;
        @endphp

        <tr>
            <td>{{ $no++ }}</td>
            <td>{{ $pp->pegawai->nama }}</td>
            <td>{{ $perjalanan->dari_kota }}</td>
            <td>{{ $perjalanan->tujuan_kota }}</td>
            <td>{{ \Carbon\Carbon::parse($perjalanan->tanggal_mulai)->format('d F Y') }}</td>
            <td>1</td>
            <td>{{ number_format($totalPegawai,0,',','.') }}</td>
            <td>{{ number_format($totalPegawai,0,',','.') }}</td>
            <td>{{ number_format($totalPegawai,0,',','.') }}</td>
            <td>-</td>
        </tr>

    @endforeach

        <tr>
            <td colspan="8"><strong>Jumlah</strong></td>
            <td><strong>{{ number_format($grand,0,',','.') }}</strong></td>
            <td></td>
        </tr>

    </tbody>
</table>
