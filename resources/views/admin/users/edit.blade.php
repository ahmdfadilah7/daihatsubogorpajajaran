@extends('layouts.admin')

@section('title', 'Edit Pengguna')
@section('heading', 'Edit Pengguna')

@section('content')
    <x-admin.form-shell
        title="Edit Pengguna"
        description="Perbarui informasi akun {{ $user->name }}."
        :back="route('admin.users.index')"
        back-label="Kembali ke daftar"
        icon="fa-user-pen">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf @method('PUT')
            @include('admin.users._form')
        </form>
    </x-admin.form-shell>
@endsection
