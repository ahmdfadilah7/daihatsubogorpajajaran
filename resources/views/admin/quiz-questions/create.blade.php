@extends('layouts.admin')

@section('title', 'Tambah Pertanyaan')
@section('heading', 'Tambah Pertanyaan Kuis')

@section('content')
    <x-admin.form-shell
        title="Tambah Pertanyaan Kuis"
        description="Susun pertanyaan beserta pilihan jawaban dan skor per model."
        :back="route('admin.quiz-questions.index')"
        back-label="Kembali ke daftar"
        icon="fa-circle-question">
        <form action="{{ route('admin.quiz-questions.store') }}" method="POST">
            @csrf
            @include('admin.quiz-questions._form')
        </form>
    </x-admin.form-shell>
@endsection
