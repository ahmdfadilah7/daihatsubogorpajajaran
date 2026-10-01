@extends('layouts.admin')

@section('title', 'Edit Kategori')
@section('heading', 'Edit Gaya Kategori')

@section('content')
    <x-admin.form-shell
        title="Edit Gaya Kategori"
        description="Perbarui label dan warna latar kategori {{ $categoryStyle->category }}."
        :back="route('admin.category-styles.index')"
        back-label="Kembali ke daftar"
        icon="fa-palette"
        max-width="max-w-2xl">
        <form action="{{ route('admin.category-styles.update', $categoryStyle) }}" method="POST">
            @csrf @method('PUT')
            @include('admin.category-styles._form')
        </form>
    </x-admin.form-shell>
@endsection
