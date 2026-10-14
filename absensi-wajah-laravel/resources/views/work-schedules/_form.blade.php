@php
    $byDay = collect($schedule?->days ?? [])->keyBy('weekday');
    $isNew = $schedule === null;
@endphp
<div class="form-grid">
    <div class="field">
        <label for="name">Nama Jadwal</label>
        <input type="text" id="name" name="name" maxlength="100" value="{{ old('name', $schedule?->name) }}" required>
        @error('name') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label for="late_tolerance_minutes">Toleransi Terlambat (menit)</label>
        <input type="number" id="late_tolerance_minutes" name="late_tolerance_minutes" min="0" max="180"
               value="{{ old('late_tolerance_minutes', $schedule?->late_tolerance_minutes ?? 0) }}" required>
        @error('late_tolerance_minutes') <div class="field-error">{{ $message }}</div> @enderror
    </div>
</div>

<h3>Jam Kerja per Hari</h3>
<p class="muted">Centang hari kerja. Jam pulang lebih awal dari jam masuk berarti shift melewati tengah malam.</p>
<div class="table-wrap">
    <table class="data-table" style="min-width:520px">
        <thead><tr><th>Hari</th><th>Masuk</th><th>Pulang</th></tr></thead>
        <tbody>
            @foreach ($days as $w => $label)
                @php($d = $byDay->get($w))
                <tr>
                    <td>
                        <label class="check" style="margin:0">
                            <input type="checkbox" name="day_{{ $w }}_on" value="1"
                                   @checked(old("day_{$w}_on", $d ? 1 : ($isNew && $w < 5 ? 1 : 0)))>
                            {{ $label }}
                        </label>
                    </td>
                    <td class="field" style="margin:0"><input type="time" name="day_{{ $w }}_start" value="{{ old("day_{$w}_start", $d?->start_time) }}"></td>
                    <td class="field" style="margin:0"><input type="time" name="day_{{ $w }}_end" value="{{ old("day_{$w}_end", $d?->end_time) }}"></td>
                </tr>
                @foreach (["day_{$w}", "day_{$w}_start", "day_{$w}_end"] as $key)
                    @error($key) <tr><td colspan="3" class="field-error">{{ $message }}</td></tr> @enderror
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
@error('days') <div class="field-error">{{ $message }}</div> @enderror

<div class="form-actions" style="margin-top:18px">
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('work-schedules.index') }}" class="btn btn-light">Batal</a>
</div>
