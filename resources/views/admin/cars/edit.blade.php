@extends('layouts.admin')

@section('title', 'Edit Mobil')
@section('heading', 'Edit Mobil')

@section('content')
    <div class="bg-white rounded-lg border border-slate-200 p-6 max-w-3xl">
        <form action="{{ route('admin.cars.update', $car) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.cars._form')
        </form>
    </div>
@endsection
