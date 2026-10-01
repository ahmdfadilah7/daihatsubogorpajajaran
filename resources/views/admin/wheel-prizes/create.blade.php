@extends('layouts.admin')

@section('title', 'Tambah Hadiah')
@section('heading', 'Tambah Hadiah Roda')

@section('content')
    <x-admin.form-shell
        title="Tambah Hadiah Roda"
        description="Tambahkan hadiah baru untuk fitur roda keberuntungan."
        :back="route('admin.wheel-prizes.index')"
        back-label="Kembali ke daftar"
        icon="fa-gift">
        <form action="{{ route('admin.wheel-prizes.store') }}" method="POST">
            @csrf
            @include('admin.wheel-prizes._form')
        </form>
    </x-admin.form-shell>
@endsection
