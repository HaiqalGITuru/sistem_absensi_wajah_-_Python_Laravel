@php($hideEmployee = $hideEmployee ?? false)
<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                @unless ($hideEmployee)<th>Karyawan</th><th>Departemen</th>@endunless
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows ?? [] as $r)
                <tr>
                    <td style="white-space:nowrap">{{ $r['work_date']?->format('d-m-Y') ?? '-' }}</td>
                    @unless ($hideEmployee)
                        <td><strong>{{ $r['employee_name'] ?? '-' }}</strong><br><span class="muted">{{ $r['employee_code'] }}</span></td>
                        <td>{{ $r['department'] ?? '-' }}</td>
                    @endunless
                    <td>
                        {{ $r['check_in']?->format('H:i') ?? '-' }}
                        @if (($r['raw']['check_in_method'] ?? null) === 'admin') <span class="badge badge-blue">admin</span> @endif
                    </td>
                    <td>
                        {{ $r['check_out']?->format('H:i') ?? '-' }}
                        @if ($r['raw']['left_early'] ?? false) <span class="badge badge-yellow">pulang cepat</span> @endif
                    </td>
                    <td>
                        <span class="badge {{ \App\Support\AttendanceStatus::badge($r['status']) }}">
                            {{ \App\Support\AttendanceStatus::label($r['status']) }}
                            @if ($r['status'] === 'terlambat' && ! empty($r['raw']['late_minutes'])) {{ $r['raw']['late_minutes'] }} mnt @endif
                        </span>
                    </td>
                    <td><a href="{{ route('attendance.show', ['id' => $r['id'], 'source' => $r['source']]) }}" class="btn btn-light btn-sm">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="{{ $hideEmployee ? 5 : 7 }}" class="empty">Belum ada data absensi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
