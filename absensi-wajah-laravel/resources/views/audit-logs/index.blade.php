@extends('layouts.app')

@section('title', 'Audit Log')
@section('subtitle', 'Riwayat aktivitas admin')

@section('content')
@include('partials.source-notice')
<div class="panel">
    <form method="GET" action="{{ route('audit-logs.index') }}" class="filter-form" style="margin-bottom:20px">
        <div class="field">
            <label for="action">Aksi</label>
            <select id="action" name="action">
                <option value="">Semua</option>
                @foreach ($actions as $a)
                    <option value="{{ $a }}" @selected($action === $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="q">Cari</label>
            <input type="text" id="q" name="q" value="{{ $search }}" placeholder="Deskripsi / user">
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('audit-logs.index') }}" class="btn btn-light">Reset</a>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>User</th>
                    <th>Aksi</th>
                    <th>Entitas</th>
                    <th>Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs ?? [] as $log)
                    <tr>
                        <td style="white-space:nowrap">{{ $log->created_at?->format('d-m-Y H:i:s') }}</td>
                        <td>{{ $log->user?->name ?? (str_contains((string) $log->description, 'via API') ? 'API' : 'Sistem') }}</td>
                        <td>
                            <span class="badge {{ \App\Support\Format::auditBadge($log->action) }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td>
                            {{ $log->entity_type ?? '-' }}
                            @if ($log->entity_id)
                                <span class="muted">#{{ $log->entity_id }}</span>
                            @endif
                        </td>
                        <td>{{ $log->description ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Belum ada aktivitas tercatat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($logs)
        {{ $logs->links('partials.pagination') }}
    @endif
</div>
@endsection