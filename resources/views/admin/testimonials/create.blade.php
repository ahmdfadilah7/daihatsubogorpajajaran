@extends('layouts.admin')

@section('title', 'Tambah Testimoni')
@section('heading', 'Tambah Testimoni')

@section('content')
    <div class="bg-white rounded-lg border border-slate-200 p-6 max-w-3xl">
        <form action="{{ route('admin.testimonials.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.testimonials._form')
        </form>
    </div>
@endsection
