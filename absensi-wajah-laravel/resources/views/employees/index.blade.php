@extends('layouts.app')

@section('title', 'Karyawan')
@section('subtitle', 'Kelola data karyawan')

@section('content')
@include('partials.source-notice')
<div class="panel">
    <div class="toolbar">
        <form method="GET" action="{{ route('employees.index') }}" class="filter-form">
            <div class="field">
                <label for="q">Cari</label>
                <input type="text" id="q" name="q" value="{{ $search }}" placeholder="Kode / nama">
            </div>
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="aktif" @selected($status === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option>
                    <option value="" @selected($status === '')>Semua</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('employees.index') }}" class="btn btn-light">Reset</a>
        </form>

        <a href="{{ route('employees.create') }}" class="btn btn-primary">+ Tambah Karyawan</a>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Departemen</th>
                    <th>Jadwal Kerja</th>
                    <th>Wajah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employees ?? [] as $employee)
                    <tr>
                        <td>{{ $employee->employee_code }}</td>
                        <td><strong>{{ $employee->full_name }}</strong></td>
                        <td>{{ $employee->department }}</td>
                        <td>{{ $employee->workSchedule?->name ?? '-' }}</td>
                        <td>
                            @php($fc = $employee->face_templates_count ?? 0)
                            <span class="badge {{ $fc > 0 ? 'badge-green' : 'badge-gray' }}">
                                {{ $fc > 0 ? 'Terdaftar (' . $fc . ')' : 'Belum' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $employee->is_active ? 'badge-green' : 'badge-gray' }}">
                                {{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('employees.show', $employee) }}" class="btn btn-light btn-sm">Detail</a>
                                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning btn-sm">Edit</a>

                                <form method="POST" action="{{ route('employees.toggle', $employee) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $employee->is_active ? 'btn-light' : 'btn-success' }}">
                                        {{ $employee->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">Belum ada data karyawan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($employees)
        {{ $employees->links('partials.pagination') }}
    @endif
</div>
@endsection