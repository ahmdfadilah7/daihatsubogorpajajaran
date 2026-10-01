@extends('layouts.admin')

@section('title', 'Edit Mobil')
@section('heading', 'Edit Mobil')

@section('content')
    <x-admin.form-shell
        title="Edit Mobil"
        description="Perbarui informasi mobil {{ $car->model }}."
        :back="route('admin.cars.index')"
        back-label="Kembali ke daftar"
        icon="fa-car">
        <form action="{{ route('admin.cars.update', $car) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.cars._form')
        </form>
    </x-admin.form-shell>
@endsection
