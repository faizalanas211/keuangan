<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Slip Gaji</title>

<style>
    @page {
        size: 21cm 14cm;
        margin: 8mm 10mm;
    }

    body {
        font-family: Tahoma, DejaVu Sans, sans-serif;
        font-size: 9pt;
        line-height: 1;
        margin: 0;
        padding: 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        page-break-inside: avoid;
    }

    td {
        padding: 1px 3px;
        vertical-align: top;
    }

    p {
        margin: 0;
        padding: 0;
    }

    .bold { font-weight: bold; }
    .right { text-align: right; }

    .header-right img {
        height: 50px;
        margin-left: 2px;
    }

    .divider {
        border-top: 1px solid #000;
        margin: 4px 0;
    }

    .section-title {
        font-weight: bold;
        margin-bottom: 2px;
    }

    .inner-table td {
        padding: 1px 0;
    }
</style>
</head>

<body>

<!-- HEADER -->
<table>
<tr>
    <td width="40%">
        <div class="bold">SLIP GAJI PEGAWAI</div>
        <div>{{ $periode->translatedFormat('F Y') }}</div>
        <br>

        <table>
            <tr><td width="80">Nama</td><td width="10">:</td><td>{{ $pegawai->nama }}</td></tr>
            <tr><td>NIP</td><td>:</td><td>{{ $pegawai->nip }}</td></tr>
            <tr><td>Jabatan</td><td>:</td><td>{{ $pegawai->jabatan }}</td></tr>
            <tr><td>Pangkat/Gol.</td><td>:</td><td>{{ $pegawai->pangkat_golongan }}</td></tr>
        </table>
    </td>

    <td width="50%" class="right header-right">

        <img src="{{ public_path('logo/logo_satu.png') }}" style="height: 60px;">
</td>

</tr>
</table>

<div class="divider"></div>

<!-- PENGHASILAN & POTONGAN -->
<table>
<tr>

<td width="50%">
    <div class="section-title">PENGHASILAN</div>
    <table class="inner-table">
        <tr><td>Gaji Induk</td><td class="right">{{ number_format($penghasilan->gaji_induk,0,',','.') }}</td></tr>
        <tr><td>Tunj. Suami/Istri</td><td class="right">{{ number_format($penghasilan->tunj_suami_istri,0,',','.') }}</td></tr>
        <tr><td>Tunj. Anak</td><td class="right">{{ number_format($penghasilan->tunj_anak,0,',','.') }}</td></tr>
        <tr><td>Tunj. Umum</td><td class="right">{{ number_format($penghasilan->tunj_umum,0,',','.') }}</td></tr>
        <tr><td>Tunj. Struktural</td><td class="right">{{ number_format($penghasilan->tunj_struktural,0,',','.') }}</td></tr>
        <tr><td>Tunj. Fungsional</td><td class="right">{{ number_format($penghasilan->tunj_fungsional,0,',','.') }}</td></tr>
        <tr><td>Tunj. Beras</td><td class="right">{{ number_format($penghasilan->tunj_beras,0,',','.') }}</td></tr>
        <tr><td>Tunj. Pajak</td><td class="right">{{ number_format($penghasilan->tunj_pajak,0,',','.') }}</td></tr>
        <tr><td>Pembulatan</td><td class="right">{{ number_format($penghasilan->pembulatan,0,',','.') }}</td></tr>
    </table>
</td>

<td width="50%">
    <div class="section-title">POTONGAN</div>
    <table class="inner-table">
        <tr><td>Potongan Wajib</td><td class="right">{{ number_format($potongan->potongan_wajib ?? 0,0,',','.') }}</td></tr>
        <tr><td>Potongan Pajak</td><td class="right">{{ number_format($potongan->potongan_pajak ?? 0,0,',','.') }}</td></tr>
        <tr><td>Potongan BPJS</td><td class="right">{{ number_format($potongan->potongan_bpjs ?? 0,0,',','.') }}</td></tr>
        <tr><td>Potongan BPJS Lain</td><td class="right">{{ number_format($potongan->potongan_bpjs_lain ?? 0,0,',','.') }}</td></tr>
        <tr><td>Dana Sosial</td><td class="right">{{ number_format($potongan->dana_sosial ?? 0,0,',','.') }}</td></tr>
        <tr><td>Bank Jateng</td><td class="right">{{ number_format($potongan->bank_jateng ?? 0,0,',','.') }}</td></tr>
        <tr><td>Bank BJB</td><td class="right">{{ number_format($potongan->bank_bjb ?? 0,0,',','.') }}</td></tr>
        <tr><td>Parcel</td><td class="right">{{ number_format($potongan->parcel ?? 0,0,',','.') }}</td></tr>
        <tr><td>Kop. Sayuk Rukun</td><td class="right">{{ number_format($potongan->kop_sayuk_rukun ?? 0,0,',','.') }}</td></tr>
        <tr><td>Kop. Mitra Lingua</td><td class="right">{{ number_format($potongan->kop_mitra_lingua ?? 0,0,',','.') }}</td></tr>
    </table>
</td>

</tr>
</table>


<!-- TOTAL -->
<table>
<tr class="bold">
    <td width="40%">Total Penghasilan</td>
    <td width="10%" class="right">{{ number_format($totalPenghasilan,0,',','.') }}</td>
    <td width="0%">Total Potongan</td>
    <td class="right">{{ number_format($totalPotongan,0,',','.') }}</td>
</tr>
</table>

<br>
<div class="divider"></div>

<div class="bold">
    Penghasilan Bersih: Rp{{ number_format($bersih,0,',','.') }}##
</div>

<div class="bold">
    Terbilang: #{{ \App\Helpers\Terbilang::convert($bersih) }} Rupiah#
</div>

<br>

<div class="right">
    Ungaran, 1 {{ $periode->translatedFormat('F Y') }}<br><br>
    <b>PPABP</b>
</div>

</body>
</html>
