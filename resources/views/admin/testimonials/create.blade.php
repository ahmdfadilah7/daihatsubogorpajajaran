@extends('layouts.admin')

@section('title', 'Tambah Testimoni')
@section('heading', 'Tambah Testimoni')

@section('content')
    <x-admin.form-shell
        title="Tambah Testimoni"
        description="Tambahkan ulasan pelanggan untuk ditampilkan di situs."
        :back="route('admin.testimonials.index')"
        back-label="Kembali ke daftar"
        icon="fa-comment-dots">
        <form action="{{ route('admin.testimonials.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.testimonials._form')
        </form>
    </x-admin.form-shell>
@endsection
