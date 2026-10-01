@extends('layouts.admin')

@section('title', 'Tambah Pengguna')
@section('heading', 'Tambah Pengguna')

@section('content')
    <x-admin.form-shell
        title="Tambah Pengguna"
        description="Buat akun pengguna baru untuk mengakses dashboard admin."
        :back="route('admin.users.index')"
        back-label="Kembali ke daftar"
        icon="fa-user-plus">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            @include('admin.users._form')
        </form>
    </x-admin.form-shell>
@endsection
