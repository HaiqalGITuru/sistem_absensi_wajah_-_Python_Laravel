@php
    $cfg = config('report');
    $logoPath = $cfg['logo'] ? public_path($cfg['logo']) : null;
    $hasLogo = $logoPath && is_file($logoPath);
    $fromLabel = \Illuminate\Support\Carbon::parse($from)->format('d-m-Y');
    $toLabel = \Illuminate\Support\Carbon::parse($to)->format('d-m-Y');
    $printed = \Illuminate\Support\Carbon::now();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Rekap Absensi</title>
    <style>
        @page { margin: 30px 48px 40px; }
        body { font-family: DejaVu Serif, serif; font-size: 10px; color: #000; line-height: 1.45; }

        .kop { width: 100%; border-collapse: collapse; font-family: DejaVu Sans, sans-serif; }
        .kop td { border: 0; padding: 0; vertical-align: middle; }
        .kop .logo { width: 70px; }
        .kop .logo img { width: 62px; }
        .kop .text { text-align: center; }
        .kop .l1 { font-size: 13px; }
        .kop .l2 { font-size: 16px; font-weight: bold; }
        .kop .l3 { font-size: 9px; }
        .rule { border-top: 3px solid #333; border-bottom: 1px solid #333; height: 2px; margin: 6px 0 16px; }

        .meta { border-collapse: collapse; margin-bottom: 12px; }
        .meta td { border: 0; padding: 1px 0; vertical-align: top; }
        .meta .k { width: 85px; }
        .meta .c { width: 12px; }

        .title { text-align: center; font-weight: bold; font-size: 12px; text-decoration: underline; margin: 0 0 12px; }
        p { margin: 0 0 10px; text-align: justify; }

        table.data { width: 100%; border-collapse: collapse; margin: 6px 0 14px; }
        table.data th, table.data td { border: 1px solid #000; padding: 4px 5px; font-size: 9px; }
        table.data th { text-align: center; font-weight: bold; }
        table.data td.c, table.data th.c { text-align: center; }

        .sign { width: 100%; border-collapse: collapse; margin-top: 18px; page-break-inside: avoid; }
        .sign td { border: 0; padding: 0; }
        .sign .box { width: 42%; text-align: center; }
        .sign .space { height: 62px; }
        .sign .name { font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
    <table class="kop">
        <tr>
            @if ($hasLogo)
                <td class="logo"><img src="{{ $logoPath }}" alt=""></td>
            @endif
            <td class="text">
                <div class="l1">{{ $cfg['org_line1'] }}</div>
                <div class="l2">{{ $cfg['org_line2'] }}</div>
                <div class="l3">{{ $cfg['address'] }}</div>
                <div class="l3">{{ $cfg['contact'] }}</div>
            </td>
            @if ($hasLogo)
                <td class="logo"></td>
            @endif
        </tr>
    </table>
    <div class="rule"></div>

    <table class="meta">
        <tr><td class="k">Perihal</td><td class="c">:</td><td><strong>Laporan Rekap Absensi Karyawan</strong></td></tr>
        <tr><td class="k">Periode</td><td class="c">:</td><td>{{ $fromLabel }} s/d {{ $toLabel }}</td></tr>
        <tr><td class="k">Departemen</td><td class="c">:</td><td>{{ $department !== '' ? $department : 'Semua departemen' }}</td></tr>
    </table>

    <p>
        Dengan hormat, berikut kami sampaikan rekapitulasi data absensi karyawan
        pada periode <strong>{{ $fromLabel }}</strong> sampai dengan <strong>{{ $toLabel }}</strong>
        berdasarkan data kehadiran yang tercatat pada sistem absensi wajah.
    </p>

    <table class="data">
        <thead>
            <tr>
                <th style="width:24px">NO</th>
                <th>KODE</th>
                <th>NAMA</th>
                <th>DEPARTEMEN</th>
                <th>TOTAL SESI</th>
                <th>HADIR</th>
                <th>TERLAMBAT</th>
                <th>TIDAK HADIR</th>
                <th>JAM KERJA</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $i => $r)
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td>{{ $r->employee_code }}</td>
                    <td>{{ $r->full_name }}</td>
                    <td>{{ $r->department }}</td>
                    <td class="c">{{ $r->total_sessions }}</td>
                    <td class="c">{{ $r->present_count }}</td>
                    <td class="c">{{ $r->late_count }}</td>
                    <td class="c">{{ $r->absent_count }}</td>
                    <td class="c">{{ \App\Support\Format::minutes((int) $r->work_minutes) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="c">Tidak ada data pada periode ini.</td></tr>
            @endforelse
        </tbody>
        @if ($rows->count() > 0)
            <tfoot>
                <tr>
                    <th colspan="4">TOTAL</th>
                    <th>{{ $totals['total_sessions'] }}</th>
                    <th>{{ $totals['present_count'] }}</th>
                    <th>{{ $totals['late_count'] }}</th>
                    <th>{{ $totals['absent_count'] }}</th>
                    <th>{{ \App\Support\Format::minutes($totals['work_minutes']) }}</th>
                </tr>
            </tfoot>
        @endif
    </table>

    <p>Demikian laporan ini kami sampaikan, atas perhatiannya kami ucapkan terima kasih.</p>

    <table class="sign">
        <tr>
            <td></td>
            <td class="box">
                {{ $cfg['city'] }}, {{ $printed->format('d-m-Y') }}<br>
                {{ $cfg['signer_title'] }}
                <div class="space"></div>
                <div class="name">{{ $cfg['signer_name'] !== '' ? $cfg['signer_name'] : '(................................)' }}</div>
                @if ($cfg['signer_id'] !== '')
                    <div>{{ $cfg['signer_id'] }}</div>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
