@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Selamat datang, ' . auth()->user()->name)

@push('styles')
<style>
    .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .card {
        background: #ffffff;
        border-radius: 14px;
        padding: 22px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    .card-title { color: #6b7280; font-size: 14px; margin-bottom: 10px; }
    .card-value { font-size: 32px; font-weight: 700; }
    .card-note { color: #94a3b8; font-size: 12px; margin-top: 6px; }

    .grid-2 {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    .chart {
        display: flex;
        align-items: flex-end;
        gap: 14px;
        height: 180px;
        padding-top: 10px;
    }

    .chart .col {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        height: 100%;
    }

    .chart .bar {
        width: 100%;
        max-width: 46px;
        background: #2563eb;
        border-radius: 6px 6px 0 0;
        min-height: 3px;
    }

    .chart .val { font-size: 12px; font-weight: 700; margin-bottom: 4px; }
    .chart .lbl { font-size: 11px; color: #64748b; margin-top: 6px; }

    .list-item {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
    }

    @media (max-width: 900px) {
        .grid-2 { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
   @if (!empty($apiError))
        <div class="alert alert-warning"><strong>Data dashboard tidak dapat diambil.</strong> {{ $apiError }}
            <a href="{{ route('python-api.status') }}">Periksa status API</a></div>
    @endif

    <div class="stats">

        <div class="card">
            <div class="card-title">Karyawan Aktif</div>
            <div class="card-value">{{ $totalEmployees }}</div>
            <div class="card-note">{{ $activeSchedules }} jadwal kerja aktif</div>
        </div>

        <div class="card">
            <div class="card-title">Check-in Hari Ini</div>
            <div class="card-value">{{ $checkInsToday }}</div>
        </div>

        <div class="card">
            <div class="card-title">Check-out Hari Ini</div>
            <div class="card-value">{{ $checkOutsToday }}</div>
        </div>

        <div class="card">
            <div class="card-title">Terlambat Hari Ini</div>
            <div class="card-value">{{ $lateToday }}</div>
        </div>

        <div class="card">
            <div class="card-title">Belum Check-in</div>
            <div class="card-value">{{ $notCheckedInCount }}</div>
        </div>

        <div class="card">
            <div class="card-title">Python API</div>
            <div class="card-value" style="font-size:18px">
                <span id="api-badge" class="badge badge-gray">Memeriksa...</span>
            </div>
            <div class="card-note" id="api-note">&nbsp;</div>
        </div>

    </div>

    <div class="grid-2">

        <div class="panel" style="margin-bottom:0">
            <h3>Check-in 7 Hari Terakhir</h3>

            <div class="chart">
                @foreach ($weekly as $day)
                    <div class="col">
                        <div class="val">{{ $day['value'] }}</div>
                        <div class="bar" style="height: {{ $day['percent'] }}%"></div>
                        <div class="lbl">{{ $day['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="panel" style="margin-bottom:0">
            <h3>Belum Check-in Hari Ini</h3>

            @forelse ($notCheckedIn as $emp)
                <div class="list-item">
                    <span>{{ $emp->full_name }}</span>
                    <span class="muted">{{ $emp->department }}</span>
                </div>
            @empty
                <div class="empty">Semua karyawan aktif sudah check-in.</div>
            @endforelse

            @if ($notCheckedInCount > $notCheckedIn->count())
                <div class="muted" style="margin-top:10px">
                    dan {{ $notCheckedInCount - $notCheckedIn->count() }} lainnya.
                </div>
            @endif
        </div>

    </div>

    <div class="panel">

        <div class="toolbar" style="margin-bottom:10px">
            <h3 style="margin:0">Absensi Terbaru Hari Ini</h3>
            <a href="{{ route('attendance.today') }}" class="btn btn-light btn-sm">Lihat semua</a>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Departemen</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($latestAttendance as $attendance)
                        <tr>
                            <td>{{ $attendance->employee->full_name ?? '-' }}</td>
                            <td>{{ $attendance->employee->department ?? '-' }}</td>
                            <td>{{ $attendance->check_in_at ? $attendance->check_in_at->format('H:i:s') : '-' }}</td>
                            <td>{{ $attendance->check_out_at ? $attendance->check_out_at->format('H:i:s') : '-' }}</td>
                            <td>
                                <span class="badge {{ \App\Support\AttendanceStatus::badge($attendance->status) }}">
                                    {{ \App\Support\AttendanceStatus::label($attendance->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty">Belum ada data absensi hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    (function () {
        var badge = document.getElementById('api-badge');
        var note = document.getElementById('api-note');

        fetch(@json(route('python-api.check')), {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                badge.className = 'badge ' + (d.ok ? 'badge-green' : 'badge-red');
                badge.textContent = d.ok ? 'Online' : 'Offline';
                note.textContent = d.ok ? (d.ms + ' ms') : d.message;
            })
            .catch(function () {
                badge.className = 'badge badge-red';
                badge.textContent = 'Offline';
                note.textContent = 'Gagal memeriksa status.';
            });
    })();
</script>
@endpush