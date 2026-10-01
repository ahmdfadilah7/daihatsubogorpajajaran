@extends('layouts.admin')

@section('title', 'Tambah Mobil')
@section('heading', 'Tambah Mobil')

@section('content')
    <div class="bg-white rounded-lg border border-slate-200 p-6 max-w-3xl">
        <form action="{{ route('admin.cars.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.cars._form')
        </form>
    </div>
@endsection
