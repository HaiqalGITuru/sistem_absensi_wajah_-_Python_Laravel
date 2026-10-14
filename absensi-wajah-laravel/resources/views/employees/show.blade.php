@extends('layouts.app')

@section('title', $employee->full_name)
@section('subtitle', 'Kode ' . $employee->employee_code)

@section('content')
<div class="panel">
    <div class="toolbar">
        <h3 style="margin:0">Data Karyawan</h3>
        <div class="actions">
            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning btn-sm">Edit</a>
            <form method="POST" action="{{ route('employees.toggle', $employee) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm {{ $employee->is_active ? 'btn-light' : 'btn-success' }}">
                    {{ $employee->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
            </form>
        </div>
    </div>
    <dl class="detail-list">
        <dt>Kode (NIP)</dt><dd>{{ $employee->employee_code }}</dd>
        <dt>Nama</dt><dd>{{ $employee->full_name }}</dd>
        <dt>Departemen</dt><dd>{{ $employee->department }}</dd>
        <dt>Jadwal kerja</dt><dd>{{ $employee->workSchedule?->name ?? '-' }}</dd>
        <dt>Status</dt>
        <dd><span class="badge {{ $employee->is_active ? 'badge-green' : 'badge-gray' }}">{{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}</span></dd>
        <dt>Wajah</dt>
        <dd>
            @php($fc = $employee->face_templates_count ?? 0)
            <span class="badge {{ $fc > 0 ? 'badge-green' : 'badge-gray' }}">{{ $fc > 0 ? 'Terdaftar (' . $fc . ' sampel)' : 'Belum terdaftar' }}</span>
        </dd>
    </dl>
</div>

<div class="panel">
    <h3>Pendaftaran Wajah</h3>
    <p class="muted">
        Wajah didaftarkan memakai <strong>kamera kios</strong>. Setelah tombol ditekan, karyawan ini
        berdiri sendirian 0,7–1 m di depan <strong>kamera kios</strong> (bukan di layar ini),
        menghadap kamera, cahaya cukup, tanpa masker atau kacamata gelap. Permintaan batal otomatis
        setelah 2 menit jika belum diambil kios.
    </p>

    @if (session('enroll_request_id'))
        <div id="enroll-status"
             data-url="{{ route('face-enrollments.status', ['id' => session('enroll_request_id')]) }}"
             style="margin:4px 0 14px;padding:12px 14px;border-radius:10px;background:#1f2937;color:#e5e7eb">
            Memantau status pendaftaran…
        </div>
    @endif

    @if (! $employee->is_active)
        <div class="alert alert-warning">Karyawan nonaktif. Aktifkan dulu untuk mendaftarkan wajah.</div>
    @else
        <form method="POST" action="{{ route('face-enrollments.start', $employee) }}">
            @csrf
            <div class="field" style="max-width:520px">
                <label for="replace_reason">Alasan daftar ulang (opsional — isi bila wajah sudah pernah terdaftar)</label>
                <input type="text" id="replace_reason" name="replace_reason" minlength="5" maxlength="255"
                       value="{{ old('replace_reason') }}" placeholder="mis. sering tidak dikenali, penampilan berubah">
                @error('replace_reason') <div class="field-error">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-primary">Daftarkan wajah di kamera kios</button>
        </form>
    @endif
</div>

<div class="panel">
    <h3>Absensi 30 Hari Terakhir <span class="muted">({{ $totalSessions }} hari hadir)</span></h3>
    @include('partials.attendance-table', ['rows' => $recentSessions, 'hideEmployee' => true])
</div>
@endsection

@push('scripts')
<script>
(function () {
    var box = document.getElementById('enroll-status');
    if (!box) return;
    var url = box.dataset.url;
    var labels = {
        pending: 'Menunggu kios memulai…',
        running: 'Sedang mengambil wajah di kios…',
        done: 'Berhasil didaftarkan',
        failed: 'Gagal',
        cancelled: 'Dibatalkan',
        expired: 'Kedaluwarsa — kios tidak mengambil dalam 2 menit'
    };
    function paint(bg) { box.style.background = bg; box.style.color = '#fff'; }
    function render(d) {
        var s = d.status || '';
        if (s === 'done') paint('#15803d');
        else if (s === 'failed' || s === 'cancelled' || s === 'expired') paint('#b91c1c');
        else paint('#b45309');
        var txt = (labels[s] || s);
        if ((s === 'running' || s === 'pending') && d.samples_total) {
            txt += ' (' + (d.samples_taken || 0) + '/' + d.samples_total + ')';
        }
        if (d.message) txt += ' — ' + d.message;
        box.textContent = txt;
    }
    var tries = 0;
    function poll() {
        fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.ok) { box.style.background = '#b91c1c'; box.style.color = '#fff'; box.textContent = d.message || 'Status tidak dapat diambil.'; return; }
                render(d);
                tries++;
                if (!d.finished && tries < 90) { setTimeout(poll, 2000); }
            })
            .catch(function () { box.style.background = '#b91c1c'; box.style.color = '#fff'; box.textContent = 'Gagal memeriksa status.'; });
    }
    poll();
})();
</script>
@endpush
