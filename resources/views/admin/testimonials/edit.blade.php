@extends('layouts.admin')

@section('title', 'Edit Testimoni')
@section('heading', 'Edit Testimoni')

@section('content')
    <div class="bg-white rounded-lg border border-slate-200 p-6 max-w-3xl">
        <form action="{{ route('admin.testimonials.update', $testimonial) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.testimonials._form')
        </form>
    </div>
@endsection
