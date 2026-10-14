@extends('layouts.app')

@section('title', 'Status Python API')
@section('subtitle', 'Pemeriksaan koneksi ke layanan pengenalan wajah')

@section('content')

@if ($allOk)
    <div class="alert alert-info">Semua pemeriksaan berhasil. Python API berjalan normal.</div>
@else
    <div class="alert alert-warning">
        Ada pemeriksaan yang gagal. Halaman panel yang memakai bagian itu tidak bisa menampilkan data sampai masalahnya diperbaiki. Jika pesannya "Endpoint tidak ada", nyalakan ulang server Python (scripts\jalankan_server.bat).
    </div>
@endif

<div class="panel">
    <div class="toolbar">
        <h3 style="margin:0">Hasil Pemeriksaan</h3>
        <a href="{{ route('python-api.status') }}" class="btn btn-primary">Cek Ulang</a>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Pemeriksaan</th>
                    <th>Status</th>
                    <th>Waktu Respons</th>
                    <th>Pesan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($checks as $c)
                    <tr>
                        <td><strong>{{ $c['name'] }}</strong></td>
                        <td>
                            <span class="badge {{ $c['ok'] ? 'badge-green' : 'badge-red' }}">
                                {{ $c['ok'] ? 'OK' : 'Gagal' }}
                            </span>
                        </td>
                        <td>{{ $c['ms'] }} ms</td>
                        <td>{{ $c['message'] ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="muted" style="margin-top:14px">
        Diperiksa pada {{ $checkedAt->format('d-m-Y H:i:s') }}
    </p>
</div>

<div class="panel">
    <h3>Konfigurasi</h3>

    <dl class="detail-list">
        <dt>Host</dt><dd>{{ $host ?: '-' }}</dd>
        <dt>Timeout</dt><dd>{{ $timeout }} detik</dd>
        <dt>API Token</dt>
        <dd>
            <span class="badge {{ $tokenSet ? 'badge-green' : 'badge-gray' }}">
                {{ $tokenSet ? 'Sudah diatur' : 'Belum diatur' }}
            </span>
        </dd>
    </dl>

    <p class="muted" style="margin-top:14px">
        Ubah pengaturan lewat <code>PYTHON_API_URL</code>, <code>PYTHON_API_TOKEN</code>,
        dan <code>PYTHON_API_TIMEOUT</code> di file <code>.env</code>, lalu jalankan
        <code>php artisan config:clear</code>.
    </p>
</div>
@endsection