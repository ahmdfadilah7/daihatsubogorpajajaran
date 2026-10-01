@extends('layouts.admin')

@section('title', 'Tambah Slide Hero')
@section('heading', 'Tambah Slide Hero')

@section('content')
    <div class="bg-white rounded-lg border border-slate-200 p-6 max-w-xl">
        <form action="{{ route('admin.hero-slides.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.hero-slides._form')
        </form>
    </div>
@endsection
