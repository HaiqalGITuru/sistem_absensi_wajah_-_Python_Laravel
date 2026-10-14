@if (! empty($notice))
    <div class="alert alert-warning">
        <strong>Data tidak dapat diambil dari sistem absensi.</strong> {{ $notice }}
        <a href="{{ route('python-api.status') }}">Periksa status API</a>
    </div>
@endif
