@extends('layouts.admin')

@section('title', 'Edit Pertanyaan')
@section('heading', 'Edit Pertanyaan Kuis')

@section('content')
    <x-admin.form-shell
        title="Edit Pertanyaan Kuis"
        description="Perbarui pertanyaan, pilihan jawaban, dan skor per model."
        :back="route('admin.quiz-questions.index')"
        back-label="Kembali ke daftar"
        icon="fa-circle-question">
        <form action="{{ route('admin.quiz-questions.update', $question) }}" method="POST">
            @csrf @method('PUT')
            @include('admin.quiz-questions._form')
        </form>
    </x-admin.form-shell>
@endsection
