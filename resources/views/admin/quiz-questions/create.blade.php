@extends('layouts.admin')

@section('title', 'Tambah Pertanyaan')
@section('heading', 'Tambah Pertanyaan Kuis')

@section('content')
    <div class="bg-white rounded-lg border border-slate-200 p-6 max-w-3xl">
        <form action="{{ route('admin.quiz-questions.store') }}" method="POST">
            @csrf
            @include('admin.quiz-questions._form')
        </form>
    </div>
@endsection
