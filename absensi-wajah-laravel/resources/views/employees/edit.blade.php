@extends('layouts.app')

@section('title', 'Edit Karyawan')
@section('subtitle', $employee->employee_code . ' - ' . $employee->full_name)

@section('content')
<div class="panel">
    <form method="POST" action="{{ route('employees.update', $employee) }}">
        @csrf
        @method('PUT')
        @include('employees._form')
    </form>
</div>
@endsection