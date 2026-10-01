@extends('layouts.admin')

@section('title', 'Tambah Slide Hero')
@section('heading', 'Tambah Slide Hero')

@section('content')
    <x-admin.form-shell
        title="Tambah Slide Hero"
        description="Buat slide baru untuk carousel utama di beranda."
        :back="route('admin.hero-slides.index')"
        back-label="Kembali ke daftar"
        icon="fa-images">
        <form action="{{ route('admin.hero-slides.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.hero-slides._form')
        </form>
    </x-admin.form-shell>
@endsection
