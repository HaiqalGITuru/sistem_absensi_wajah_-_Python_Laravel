@extends('layouts.app')

@php
    $ss = !empty($record['raw']['scheduled_start']) ? \Illuminate\Support\Carbon::parse($record['raw']['scheduled_start']) : null;
    $se = !empty($record['raw']['scheduled_end']) ? \Illuminate\Support\Carbon::parse($record['raw']['scheduled_end']) : null;
@endphp

@section('title', 'Detail Absensi')
@section('subtitle', ($record['employee_name'] ?? '-') . ' — ' . ($record['work_date']?->format('d-m-Y') ?? '-'))

@section('content')
<div class="panel">
    <dl class="detail-list">
        <dt>Karyawan</dt><dd>{{ $record['employee_code'] }} — {{ $record['employee_name'] ?? '-' }}</dd>
        <dt>Departemen</dt><dd>{{ $record['department'] ?? '-' }}</dd>
        <dt>Tanggal kerja</dt><dd>{{ $record['work_date']?->translatedFormat('l, d F Y') ?? '-' }}</dd>
        <dt>Jadwal</dt><dd>{{ $ss?->format('H:i') ?? '-' }} – {{ $se?->format('H:i') ?? '-' }}</dd>
        <dt>Masuk</dt><dd>{{ $record['check_in']?->format('d-m-Y H:i:s') ?? '-' }} <span class="muted">({{ $record['raw']['check_in_method'] ?? '-' }})</span></dd>
        <dt>Pulang</dt><dd>{{ $record['check_out']?->format('d-m-Y H:i:s') ?? '-' }} <span class="muted">({{ $record['raw']['check_out_method'] ?? '-' }})</span></dd>
        <dt>Status</dt>
        <dd>
            <span class="badge {{ \App\Support\AttendanceStatus::badge($record['status']) }}">{{ \App\Support\AttendanceStatus::label($record['status']) }}</span>
            @if (!empty($record['raw']['late_minutes'])) <span class="muted">terlambat {{ $record['raw']['late_minutes'] }} menit</span> @endif
            @if ($record['raw']['left_early'] ?? false) <span class="badge badge-yellow">pulang cepat</span> @endif
        </dd>
    </dl>
    <p class="muted" style="margin-top:16px">
        Koreksi dan pembatalan absensi dilakukan di admin sistem absensi (menu Absensi → Koreksi),
        karena alasannya wajib tercatat di audit log.
    </p>
    <a href="{{ url()->previous() }}" class="btn btn-light">Kembali</a>
</div>
@endsection
