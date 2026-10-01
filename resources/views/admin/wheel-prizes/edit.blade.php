@extends('layouts.admin')

@section('title', 'Edit Hadiah')
@section('heading', 'Edit Hadiah Roda')

@section('content')
    <x-admin.form-shell
        title="Edit Hadiah Roda"
        description="Perbarui hadiah {{ $prize->label }} pada roda keberuntungan."
        :back="route('admin.wheel-prizes.index')"
        back-label="Kembali ke daftar"
        icon="fa-gift">
        <form action="{{ route('admin.wheel-prizes.update', $prize) }}" method="POST">
            @csrf @method('PUT')
            @include('admin.wheel-prizes._form')
        </form>
    </x-admin.form-shell>
@endsection
