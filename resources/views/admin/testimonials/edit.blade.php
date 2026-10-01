@extends('layouts.admin')

@section('title', 'Edit Testimoni')
@section('heading', 'Edit Testimoni')

@section('content')
    <x-admin.form-shell
        title="Edit Testimoni"
        description="Perbarui ulasan dari {{ $testimonial->name }}."
        :back="route('admin.testimonials.index')"
        back-label="Kembali ke daftar"
        icon="fa-comment-dots">
        <form action="{{ route('admin.testimonials.update', $testimonial) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.testimonials._form')
        </form>
    </x-admin.form-shell>
@endsection
