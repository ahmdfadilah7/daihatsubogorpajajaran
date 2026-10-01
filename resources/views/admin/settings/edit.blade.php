@extends('layouts.admin')

@section('title', 'Pengaturan Website')
@section('heading', 'Pengaturan Website')

@section('content')
    <x-admin.form-shell
        title="Pengaturan Website"
        description="Kelola identitas situs, SEO/meta, Open Graph, dan informasi kontak."
        :back="route('admin.dashboard')"
        back-label="Kembali ke dashboard"
        icon="fa-gear"
        max-width="max-w-4xl">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.settings._form')
        </form>
    </x-admin.form-shell>
@endsection
