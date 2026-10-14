@extends('layouts.app')

@section('title', 'Edit Jadwal Kerja')
@section('subtitle', $schedule->name)

@section('content')
<div class="panel">
    <form method="POST" action="{{ route('work-schedules.update', $schedule) }}">
        @csrf
        @method('PUT')
        @include('work-schedules._form')
    </form>
</div>
@endsection
