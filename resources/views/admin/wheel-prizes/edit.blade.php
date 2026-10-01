@extends('layouts.admin')

@section('title', 'Edit Hadiah')
@section('heading', 'Edit Hadiah Roda')

@section('content')
    <div class="bg-white rounded-lg border border-slate-200 p-6 max-w-xl">
        <form action="{{ route('admin.wheel-prizes.update', $prize) }}" method="POST">
            @csrf @method('PUT')
            @include('admin.wheel-prizes._form')
        </form>
    </div>
@endsection
