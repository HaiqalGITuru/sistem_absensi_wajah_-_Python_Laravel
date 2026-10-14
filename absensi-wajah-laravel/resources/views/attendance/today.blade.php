@extends('layouts.app')

@section('title', 'Absensi Hari Ini')
@section('subtitle', now()->translatedFormat('l, d F Y'))

@section('content')
@include('partials.source-notice')
<div class="summary">
    <div class="item"><div class="label">Sudah masuk</div><div class="value">{{ $summary['checked_in'] }}</div></div>
    <div class="item"><div class="label">Sudah pulang</div><div class="value">{{ $summary['checked_out'] }}</div></div>
    <div class="item"><div class="label">Masih bekerja</div><div class="value">{{ $summary['working'] }}</div></div>
    <div class="item"><div class="label">Catatan</div><div class="value">{{ $rows->count() }}</div></div>
</div>
<div class="panel">
    @include('partials.attendance-table', ['rows' => $rows])
</div>
@endsection
