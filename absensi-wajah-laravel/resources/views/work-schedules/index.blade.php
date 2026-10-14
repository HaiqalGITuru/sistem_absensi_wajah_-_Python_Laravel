@extends('layouts.app')

@section('title', 'Jadwal Kerja')
@section('subtitle', 'Jam kerja per hari dan toleransi terlambat')

@section('content')
@include('partials.source-notice')
<div class="panel">
    <div class="toolbar">
        <span class="muted">Perubahan jadwal berlaku untuk absensi berikutnya; catatan lama tidak berubah.</span>
        <a href="{{ route('work-schedules.create') }}" class="btn btn-primary">+ Tambah Jadwal</a>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Nama</th><th>Jam Kerja</th><th>Toleransi</th><th>Karyawan Aktif</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse ($schedules as $schedule)
                    <tr>
                        <td><strong>{{ $schedule->name }}</strong></td>
                        <td>
                            @foreach ($schedule->summary ?? [] as $line)<div>{{ $line }}</div>@endforeach
                        </td>
                        <td>{{ $schedule->late_tolerance_minutes }} menit</td>
                        <td>{{ $schedule->active_employees }}</td>
                        <td><span class="badge {{ $schedule->is_active ? 'badge-green' : 'badge-gray' }}">{{ $schedule->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('work-schedules.edit', $schedule) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form method="POST" action="{{ route('work-schedules.toggle', $schedule) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $schedule->is_active ? 'btn-light' : 'btn-success' }}">
                                        {{ $schedule->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">Belum ada jadwal kerja.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
