@extends('layouts.admin')

@section('title', 'Tambah Gambar Pojok')
@section('heading', 'Tambah Gambar Pojok')

@section('content')
    <div class="bg-white rounded-lg border border-slate-200 p-6 max-w-xl">
        <form action="{{ route('admin.corner-images.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.corner-images._form')
        </form>
    </div>
@endsection
