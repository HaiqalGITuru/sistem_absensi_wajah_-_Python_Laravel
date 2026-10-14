@extends('layouts.app')

@section('title', 'Riwayat Absensi')
@section('subtitle', 'Maksimal 31 hari per pencarian')

@section('content')
@include('partials.source-notice')
<div class="panel">
    <form method="GET" action="{{ route('attendance.index') }}" class="filter-form" style="margin-bottom:20px">
        <div class="field"><label for="date_from">Dari</label><input type="date" id="date_from" name="date_from" value="{{ $from }}"></div>
        <div class="field"><label for="date_to">Sampai</label><input type="date" id="date_to" name="date_to" value="{{ $to }}"></div>
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">Semua</option>
                @foreach (\App\Support\AttendanceStatus::options() as $key => $label)
                    <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field"><label for="q">Cari</label><input type="text" id="q" name="q" value="{{ $search }}" placeholder="Kode / nama"></div>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('attendance.index') }}" class="btn btn-light">Reset</a>
    </form>
    @include('partials.attendance-table', ['rows' => $rows ?? []])
    @if ($rows)
        {{ $rows->links('partials.pagination') }}
    @endif
</div>
@endsection
