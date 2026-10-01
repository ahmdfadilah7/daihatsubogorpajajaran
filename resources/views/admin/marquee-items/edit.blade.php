@extends('layouts.admin')

@section('title', 'Edit Teks Berjalan')
@section('heading', 'Edit Teks Berjalan')

@section('content')
    <x-admin.form-shell
        title="Edit Teks Berjalan"
        description="Perbarui pil keunggulan {{ $item->text }} pada strip berjalan."
        :back="route('admin.marquee-items.index')"
        back-label="Kembali ke daftar"
        icon="fa-bullhorn">
        <form action="{{ route('admin.marquee-items.update', $item) }}" method="POST">
            @csrf @method('PUT')
            @include('admin.marquee-items._form')
        </form>
    </x-admin.form-shell>
@endsection
