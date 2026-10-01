@extends('layouts.admin')

@section('title', 'Tambah Mobil')
@section('heading', 'Tambah Mobil')

@section('content')
    <x-admin.form-shell
        title="Tambah Mobil"
        description="Lengkapi informasi mobil baru untuk ditampilkan di situs."
        :back="route('admin.cars.index')"
        back-label="Kembali ke daftar"
        icon="fa-car">
        <form action="{{ route('admin.cars.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.cars._form')
        </form>
    </x-admin.form-shell>
@endsection
