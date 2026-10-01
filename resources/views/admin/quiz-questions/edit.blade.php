@extends('layouts.admin')

@section('title', 'Edit Pertanyaan')
@section('heading', 'Edit Pertanyaan Kuis')

@section('content')
    <div class="bg-white rounded-lg border border-slate-200 p-6 max-w-3xl">
        <form action="{{ route('admin.quiz-questions.update', $question) }}" method="POST">
            @csrf @method('PUT')
            @include('admin.quiz-questions._form')
        </form>
    </div>
@endsection
