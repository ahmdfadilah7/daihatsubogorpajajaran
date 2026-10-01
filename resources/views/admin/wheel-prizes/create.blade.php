@extends('layouts.admin')

@section('title', 'Tambah Hadiah')
@section('heading', 'Tambah Hadiah Roda')

@section('content')
    <div class="bg-white rounded-lg border border-slate-200 p-6 max-w-xl">
        <form action="{{ route('admin.wheel-prizes.store') }}" method="POST">
            @csrf
            @include('admin.wheel-prizes._form')
        </form>
    </div>
@endsection
