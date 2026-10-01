@extends('layouts.admin')

@section('title', 'Edit Slide Hero')
@section('heading', 'Edit Slide Hero')

@section('content')
    <x-admin.form-shell
        title="Edit Slide Hero"
        description="Perbarui konten slide {{ $heroSlide->name }}."
        :back="route('admin.hero-slides.index')"
        back-label="Kembali ke daftar"
        icon="fa-images">
        <form action="{{ route('admin.hero-slides.update', $heroSlide) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.hero-slides._form')
        </form>
    </x-admin.form-shell>
@endsection
