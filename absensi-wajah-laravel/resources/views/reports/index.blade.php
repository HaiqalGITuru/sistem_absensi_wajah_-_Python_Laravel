@extends('layouts.app')

@section('title', 'Laporan Absensi')
@section('subtitle', 'Rekap per karyawan, periode ' . \Illuminate\Support\Carbon::parse($from)->format('d-m-Y') . ' s/d ' . \Illuminate\Support\Carbon::parse($to)->format('d-m-Y'))

@push('styles')
<style>
    @media print {
        .sidebar, .topbar, .no-print { display: none !important; }
        .content { padding: 0 !important; }
        .panel, .summary .item { box-shadow: none !important; }
        body { background: #fff !important; }
    }
</style>
@endpush

@section('content')
@php
    $query = ['date_from' => $from, 'date_to' => $to, 'department' => $department];
@endphp

<div class="panel no-print">
    <form method="GET" action="{{ route('reports.index') }}" class="filter-form">
        <div class="field">
            <label for="date_from">Dari Tanggal</label>
            <input type="date" id="date_from" name="date_from" value="{{ $from }}">
        </div>
        <div class="field">
            <label for="date_to">Sampai Tanggal</label>
            <input type="date" id="date_to" name="date_to" value="{{ $to }}">
        </div>
        <div class="field">
            <label for="department">Departemen</label>
            <select id="department" name="department">
                <option value="">Semua</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept }}" @selected($department === $dept)>{{ $dept }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Tampilkan</button>
        <a href="{{ route('reports.index') }}" class="btn btn-light">Reset (bulan ini)</a>
    </form>
</div>

<div class="summary">
    <div class="item"><div class="label">Total Sesi</div><div class="value">{{ $totals['total_sessions'] }}</div></div>
    <div class="item"><div class="label">Hadir</div><div class="value">{{ $totals['present_count'] }}</div></div>
    <div class="item"><div class="label">Terlambat</div><div class="value">{{ $totals['late_count'] }}</div></div>
    <div class="item"><div class="label">Tidak Hadir</div><div class="value">{{ $totals['absent_count'] }}</div></div>
</div>

<div class="panel">
    <div class="toolbar no-print">
        <h3 style="margin:0">Rekap per Karyawan</h3>
        <div class="actions">
            <a href="{{ route('reports.export-summary', $query) }}" class="btn btn-success">Export Rekap (CSV)</a>
            <a href="{{ route('reports.export-summary-pdf', $query) }}" class="btn btn-success">Download PDF</a>
            <button type="button" class="btn btn-light" onclick="window.print()">Cetak</button>
        </div>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Departemen</th>
                    <th>Total Sesi</th>
                    <th>Hadir</th>
                    <th>Terlambat</th>
                    <th>Tidak Hadir</th>
                    <th>Total Jam Kerja</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                    <tr>
                        <td>{{ $r->employee_code }}</td>
                        <td><strong>{{ $r->full_name }}</strong></td>
                        <td>{{ $r->department }}</td>
                        <td>{{ $r->total_sessions }}</td>
                        <td>{{ $r->present_count }}</td>
                        <td>{{ $r->late_count }}</td>
                        <td>{{ $r->absent_count }}</td>
                        <td>{{ \App\Support\Format::minutes((int) $r->work_minutes) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">Tidak ada data pada periode ini.</td></tr>
                @endforelse
            </tbody>

            @if ($rows->count() > 0)
                <tfoot>
                    <tr>
                        <th colspan="3">Total</th>
                        <th>{{ $totals['total_sessions'] }}</th>
                        <th>{{ $totals['present_count'] }}</th>
                        <th>{{ $totals['late_count'] }}</th>
                        <th>{{ $totals['absent_count'] }}</th>
                        <th>{{ \App\Support\Format::minutes($totals['work_minutes']) }}</th>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <p class="muted" style="margin-top:14px">
        Data laporan diambil dari database lokal (tabel attendance_sessions).
    </p>
</div>
@endsection