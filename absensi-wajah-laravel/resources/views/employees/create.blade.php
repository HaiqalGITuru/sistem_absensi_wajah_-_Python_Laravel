@extends('layouts.app')

@section('title', 'Tambah Karyawan')
@section('subtitle', 'Input data karyawan baru')

@section('content')
<div class="panel">
    <form method="POST" action="{{ route('employees.store') }}">
        @csrf
        @include('employees._form')
    </form>
</div>
@endsection