@extends('layouts.app')

@section('title', 'Tambah Jadwal Kerja')
@section('subtitle', 'Input jadwal kerja baru')

@section('content')
<div class="panel">
    <form method="POST" action="{{ route('work-schedules.store') }}">
        @csrf
        @include('work-schedules._form', ['schedule' => null])
    </form>
</div>
@endsection