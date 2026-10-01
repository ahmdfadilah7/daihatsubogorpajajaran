@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <p class="text-slate-600 mb-6">Selamat datang di panel input data Daihatsu Sahabat. Pilih bagian di samping untuk mengelola konten situs.</p>

    @php
        $cards = [
            ['Mobil', $counts['cars'], 'admin.cars.index', 'fa-car'],
            ['Gaya Kategori', $counts['categoryStyles'], 'admin.category-styles.index', 'fa-palette'],
            ['Pertanyaan Kuis', $counts['quizQuestions'], 'admin.quiz-questions.index', 'fa-circle-question'],
            ['Hadiah Roda', $counts['wheelPrizes'], 'admin.wheel-prizes.index', 'fa-gift'],
            ['Gambar Pojok', $counts['cornerImages'], 'admin.corner-images.index', 'fa-image'],
            ['Slide Hero', $counts['heroSlides'], 'admin.hero-slides.index', 'fa-images'],
            ['Testimoni', $counts['testimonials'], 'admin.testimonials.index', 'fa-comment-dots'],
        ];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($cards as [$label, $count, $route, $icon])
            <a href="{{ route($route) }}" class="bg-white rounded-lg border border-slate-200 p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">{{ $label }}</p>
                        <p class="text-2xl font-bold">{{ $count }}</p>
                    </div>
                    <i class="fa-solid {{ $icon }} text-2xl text-blue-500"></i>
                </div>
            </a>
        @endforeach
    </div>
@endsection
