@extends('layouts.app')

@section('title', 'Pendaftaran Wajah')
@section('subtitle', ($enrollment->employee?->full_name ?? '-') . ' — ' . ($enrollment->employee?->employee_code ?? ''))

@push('styles')
<style>
    .progress { height: 14px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin: 14px 0 8px; }
    .progress .bar { height: 100%; width: 0; background: #2563eb; transition: width .4s; }
    .enroll-status { font-size: 22px; font-weight: 700; margin: 4px 0 6px; }
</style>
@endpush

@section('content')
<div class="panel" id="enroll" data-status-url="{{ route('face-enrollments.status', $enrollment->id) }}">
    <div class="alert alert-warning" id="kiosk-offline" style="display:none">
        Layanan kamera kios tidak terdeteksi berjalan. Pastikan <code>scripts\jalankan_kios.bat</code> menyala
        di komputer kios.
    </div>

    <span id="status-badge" class="badge badge-gray">-</span>
    <div class="enroll-status" id="status-text">Memuat...</div>
    <div class="muted" id="status-message">{{ $enrollment->message }}</div>

    <div class="progress"><div class="bar" id="bar"></div></div>
    <div class="muted" id="samples">Sampel {{ $enrollment->samples_taken }}/{{ $enrollment->samples_total }}</div>

    <ol class="muted" style="margin-top:18px">
        <li>Karyawan berdiri sendirian 0,7–1 m di depan kamera kios, menghadap kamera.</li>
        <li>Layar kios menampilkan nama depan karyawan; pastikan namanya benar.</li>
        <li>Gerakkan kepala sedikit di antara sampel. Selesai dalam beberapa detik.</li>
    </ol>

    <div class="actions" style="margin-top:16px">
        <form method="POST" action="{{ route('face-enrollments.cancel', $enrollment->id) }}" id="cancel-form">
            @csrf
            <button type="submit" class="btn btn-danger">Batalkan</button>
        </form>
        <a href="{{ route('employees.show', $enrollment->employee->id) }}" class="btn btn-light" id="back-link">Kembali ke data karyawan</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var box = document.getElementById('enroll');
    var url = box.dataset.statusUrl;
    var LABELS = {
        pending: ['Menunggu kamera kios', 'badge-gray', 'menunggu'],
        running: ['Sedang mengambil sampel wajah', 'badge-blue', 'berjalan'],
        done: ['Wajah berhasil didaftarkan', 'badge-green', 'selesai'],
        failed: ['Pendaftaran gagal', 'badge-red', 'gagal'],
        cancelled: ['Dibatalkan', 'badge-gray', 'dibatalkan'],
        expired: ['Kedaluwarsa', 'badge-yellow', 'kedaluwarsa']
    };
    var timer = null;

    function render(d) {
        var info = LABELS[d.status] || [d.status, 'badge-gray', d.status];
        var badge = document.getElementById('status-badge');
        badge.className = 'badge ' + info[1];
        badge.textContent = info[2];
        document.getElementById('status-text').textContent = info[0];
        document.getElementById('status-message').textContent = d.message || '';
        var pct = d.samples_total ? Math.round(d.samples_taken / d.samples_total * 100) : 0;
        document.getElementById('bar').style.width = pct + '%';
        document.getElementById('samples').textContent = 'Sampel ' + d.samples_taken + '/' + d.samples_total;
        document.getElementById('kiosk-offline').style.display =
            (!d.finished && d.kiosk_online === false) ? 'block' : 'none';
        if (d.finished) {
            document.getElementById('cancel-form').style.display = 'none';
            clearInterval(timer);
        }
    }

    function poll() {
        fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.error) { document.getElementById('status-message').textContent = d.message; return; }
                render(d);
            })
            .catch(function () {});
    }

    poll();
    timer = setInterval(poll, 1000);
})();
</script>
@endpush
