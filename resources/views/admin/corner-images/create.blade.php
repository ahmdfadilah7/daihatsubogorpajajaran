@extends('layouts.admin')

@section('title', 'Tambah Gambar Pojok')
@section('heading', 'Tambah Gambar Pojok')

@section('content')
    <x-admin.form-shell
        title="Tambah Gambar Pojok"
        description="Unggah gambar dekoratif beserta teks alternatifnya."
        :back="route('admin.corner-images.index')"
        back-label="Kembali ke daftar"
        icon="fa-image"
        max-width="max-w-2xl">
        <form action="{{ route('admin.corner-images.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.corner-images._form')
        </form>
    </x-admin.form-shell>
@endsection
