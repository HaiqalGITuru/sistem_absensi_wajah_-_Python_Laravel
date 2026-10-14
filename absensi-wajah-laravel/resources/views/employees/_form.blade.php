@php($emp = $employee ?? null)
<div class="form-grid">
    <div class="field">
        <label for="employee_code">Kode Karyawan (NIP)</label>
        <input type="text" id="employee_code" name="employee_code" maxlength="30"
               value="{{ old('employee_code', $emp?->employee_code) }}" required>
        @error('employee_code') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label for="full_name">Nama Lengkap</label>
        <input type="text" id="full_name" name="full_name" maxlength="150"
               value="{{ old('full_name', $emp?->full_name) }}" required>
        @error('full_name') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label for="department">Departemen</label>
        <input type="text" id="department" name="department" maxlength="100"
               value="{{ old('department', $emp?->department) }}" required>
        @error('department') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label for="work_schedule_id">Jadwal Kerja</label>
        <select id="work_schedule_id" name="work_schedule_id" required>
            <option value="">— Pilih jadwal —</option>
            @foreach ($schedules as $s)
                <option value="{{ $s->id }}" @selected((string) old('work_schedule_id', $emp?->work_schedule?->id) === (string) $s->id)>
                    {{ $s->name }}{{ $s->is_active ? '' : ' (nonaktif)' }} — {{ implode(', ', $s->summary ?? []) }}
                </option>
            @endforeach
        </select>
        @error('work_schedule_id') <div class="field-error">{{ $message }}</div> @enderror
    </div>
</div>

@if (session('needs_confirmation'))
    <label class="check alert alert-warning">
        <input type="checkbox" name="confirm_duplicate_name" value="1">
        Saya sudah memeriksa: ini orang yang BERBEDA dengan nama yang sama.
    </label>
@endif

@unless ($emp)
    <label class="check">
        <input type="checkbox" name="enroll_face" value="1" @checked(old('enroll_face', '1'))>
        Daftarkan wajah di kamera kios setelah disimpan (karyawan bersiap di depan kios)
    </label>
@endunless

<p class="muted">
    Kode, nama ganda, dan jadwal diperiksa oleh sistem absensi. Wajah didaftarkan memakai kamera
    kios; foto tidak dikirim ke panel ini.
</p>

<div class="form-actions">
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ $emp ? route('employees.show', $emp) : route('employees.index') }}" class="btn btn-light">Batal</a>
</div>