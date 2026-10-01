@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-slate-900">Selamat datang di panel Daihatsu Sahabat</h2>
        <p class="text-sm text-slate-500">Ringkasan konten situs dan aktivitas terbaru.</p>
    </div>

    @php
        $cards = [
            ['Mobil', $counts['cars'], 'admin.cars.index', 'fa-car', 'from-brand-500 to-brand-700'],
            ['Gaya Kategori', $counts['categoryStyles'], 'admin.category-styles.index', 'fa-palette', 'from-amber-500 to-orange-600'],
            ['Pertanyaan Kuis', $counts['quizQuestions'], 'admin.quiz-questions.index', 'fa-circle-question', 'from-sky-500 to-blue-600'],
            ['Hadiah Roda', $counts['wheelPrizes'], 'admin.wheel-prizes.index', 'fa-gift', 'from-fuchsia-500 to-purple-600'],
            ['Gambar Pojok', $counts['cornerImages'], 'admin.corner-images.index', 'fa-image', 'from-emerald-500 to-green-600'],
            ['Slide Hero', $counts['heroSlides'], 'admin.hero-slides.index', 'fa-images', 'from-cyan-500 to-teal-600'],
            ['Testimoni', $counts['testimonials'], 'admin.testimonials.index', 'fa-comment-dots', 'from-rose-500 to-pink-600'],
        ];
    @endphp

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($cards as [$label, $count, $route, $icon, $gradient])
            <a href="{{ route($route) }}"
               class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">{{ $label }}</p>
                        <p class="mt-1 text-3xl font-bold text-slate-900">{{ $count }}</p>
                    </div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $gradient }} text-white shadow-lg">
                        <i class="fa-solid {{ $icon }} text-lg"></i>
                    </span>
                </div>
                <span class="mt-4 inline-flex items-center gap-1 text-xs font-medium text-brand-600 opacity-0 transition group-hover:opacity-100">
                    Kelola <i class="fa-solid fa-arrow-right"></i>
                </span>
            </a>
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-5">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 lg:col-span-2">
            <h3 class="mb-4 text-sm font-semibold text-slate-900">Distribusi Konten</h3>
            <div class="relative mx-auto h-64">
                <canvas id="contentDistributionChart"></canvas>
            </div>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 lg:col-span-3">
            <h3 class="mb-4 text-sm font-semibold text-slate-900">Mobil per Kategori</h3>
            <div class="relative h-64">
                <canvas id="carsByCategoryChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Recent items --}}
    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Mobil Terbaru</h3>
                <a href="{{ route('admin.cars.index') }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">Lihat semua</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($latestCars as $car)
                    <li class="flex items-center justify-between py-2.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $car->model }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $car->type }} · {{ $car->category }}</p>
                        </div>
                        <span class="shrink-0 text-sm font-semibold text-slate-700">Rp {{ number_format($car->price, 0, ',', '.') }}</span>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-slate-400">Belum ada data mobil.</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Testimoni Terbaru</h3>
                <a href="{{ route('admin.testimonials.index') }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">Lihat semua</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($latestTestimonials as $t)
                    <li class="flex items-center justify-between py-2.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $t->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $t->city }} · {{ $t->car }}</p>
                        </div>
                        <span class="shrink-0 text-sm text-amber-500">
                            @for ($i = 0; $i < (int) $t->rating; $i++)<i class="fa-solid fa-star"></i>@endfor
                        </span>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-slate-400">Belum ada testimoni.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.Chart === 'undefined') {
                return;
            }

            const brandColors = ['#e01818', '#f59e0b', '#0ea5e9', '#a855f7', '#10b981', '#06b6d4', '#f43f5e'];

            // Doughnut: overall content distribution from the 7 counts.
            @php
                $distribution = [
                    'Mobil' => $counts['cars'],
                    'Gaya Kategori' => $counts['categoryStyles'],
                    'Pertanyaan Kuis' => $counts['quizQuestions'],
                    'Hadiah Roda' => $counts['wheelPrizes'],
                    'Gambar Pojok' => $counts['cornerImages'],
                    'Slide Hero' => $counts['heroSlides'],
                    'Testimoni' => $counts['testimonials'],
                ];
            @endphp
            const distributionData = @json($distribution);

            const distCanvas = document.getElementById('contentDistributionChart');
            if (distCanvas) {
                new window.Chart(distCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(distributionData),
                        datasets: [{
                            data: Object.values(distributionData),
                            backgroundColor: brandColors,
                            borderWidth: 2,
                            borderColor: '#ffffff',
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                        },
                        cutout: '62%',
                    },
                });
            }

            // Bar: cars per category.
            const carsByCategory = @json($carsByCategory);
            const barCanvas = document.getElementById('carsByCategoryChart');
            if (barCanvas) {
                new window.Chart(barCanvas, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(carsByCategory),
                        datasets: [{
                            label: 'Jumlah Mobil',
                            data: Object.values(carsByCategory),
                            backgroundColor: '#e01818',
                            borderRadius: 6,
                            maxBarThickness: 48,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } },
                        },
                    },
                });
            }
        });
    </script>
@endpush
