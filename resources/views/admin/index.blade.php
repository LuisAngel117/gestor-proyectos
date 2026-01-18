@extends('layouts.app')

@section('title', 'Admin')

@section('sidebar')
    @include('components.sidebar')
@endsection

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Panel Superadmin</h2>
            <p class="text-sm text-gray-600 mt-1">Vista global del sistema local.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-secondary">Volver</a>
    </div>
@endsection

@section('content')
@include('admin.partials.overview')
@endsection
