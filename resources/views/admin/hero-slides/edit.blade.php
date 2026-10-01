@extends('layouts.admin')

@section('title', 'Edit Slide Hero')
@section('heading', 'Edit Slide Hero')

@section('content')
    <div class="bg-white rounded-lg border border-slate-200 p-6 max-w-xl">
        <form action="{{ route('admin.hero-slides.update', $heroSlide) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.hero-slides._form')
        </form>
    </div>
@endsection
