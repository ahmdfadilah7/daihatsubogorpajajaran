@extends('layouts.admin')

@section('title', 'Edit Gambar Pojok')
@section('heading', 'Edit Gambar Pojok')

@section('content')
    <div class="bg-white rounded-lg border border-slate-200 p-6 max-w-xl">
        <form action="{{ route('admin.corner-images.update', $cornerImage) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.corner-images._form')
        </form>
    </div>
@endsection
