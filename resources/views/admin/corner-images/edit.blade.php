@extends('layouts.admin')

@section('title', 'Edit Gambar Pojok')
@section('heading', 'Edit Gambar Pojok')

@section('content')
    <x-admin.form-shell
        title="Edit Gambar Pojok"
        description="Perbarui gambar dekoratif atau teks alternatifnya."
        :back="route('admin.corner-images.index')"
        back-label="Kembali ke daftar"
        icon="fa-image"
        max-width="max-w-2xl">
        <form action="{{ route('admin.corner-images.update', $cornerImage) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.corner-images._form')
        </form>
    </x-admin.form-shell>
@endsection
