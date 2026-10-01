@extends('layouts.admin')

@section('title', 'Tambah Teks Berjalan')
@section('heading', 'Tambah Teks Berjalan')

@section('content')
    <x-admin.form-shell
        title="Tambah Teks Berjalan"
        description="Tambahkan pil keunggulan baru untuk strip berjalan di halaman depan."
        :back="route('admin.marquee-items.index')"
        back-label="Kembali ke daftar"
        icon="fa-bullhorn">
        <form action="{{ route('admin.marquee-items.store') }}" method="POST">
            @csrf
            @include('admin.marquee-items._form')
        </form>
    </x-admin.form-shell>
@endsection
