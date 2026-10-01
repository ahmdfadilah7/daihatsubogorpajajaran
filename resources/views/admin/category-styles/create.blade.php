@extends('layouts.admin')

@section('title', 'Tambah Kategori')
@section('heading', 'Tambah Gaya Kategori')

@section('content')
    <x-admin.form-shell
        title="Tambah Gaya Kategori"
        description="Tentukan label dan warna latar untuk sebuah kategori mobil."
        :back="route('admin.category-styles.index')"
        back-label="Kembali ke daftar"
        icon="fa-palette"
        max-width="max-w-2xl">
        <form action="{{ route('admin.category-styles.store') }}" method="POST">
            @csrf
            @include('admin.category-styles._form')
        </form>
    </x-admin.form-shell>
@endsection
