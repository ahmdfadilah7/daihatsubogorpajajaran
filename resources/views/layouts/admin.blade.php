<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Daihatsu Sahabat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">
<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="w-64 bg-slate-900 text-slate-100 flex flex-col shrink-0">
        <div class="px-5 py-4 border-b border-slate-700">
            <a href="{{ route('admin.dashboard') }}" class="text-lg font-bold">Daihatsu Admin</a>
            <p class="text-xs text-slate-400 mt-1">Panel Input Data</p>
        </div>
        @php
            $nav = [
                ['admin.dashboard', 'Dashboard', 'fa-gauge', 'admin.dashboard'],
                ['admin.cars.index', 'Mobil', 'fa-car', 'admin.cars.*'],
                ['admin.category-styles.index', 'Gaya Kategori', 'fa-palette', 'admin.category-styles.*'],
                ['admin.quiz-questions.index', 'Kuis', 'fa-circle-question', 'admin.quiz-questions.*'],
                ['admin.wheel-prizes.index', 'Hadiah Roda', 'fa-gift', 'admin.wheel-prizes.*'],
                ['admin.corner-images.index', 'Gambar Pojok', 'fa-image', 'admin.corner-images.*'],
                ['admin.hero-slides.index', 'Slide Hero', 'fa-images', 'admin.hero-slides.*'],
                ['admin.testimonials.index', 'Testimoni', 'fa-comment-dots', 'admin.testimonials.*'],
            ];
        @endphp
        <nav class="flex-1 py-3 space-y-1">
            @foreach ($nav as [$route, $label, $icon, $pattern])
                <a href="{{ route($route) }}"
                   class="flex items-center gap-3 px-5 py-2.5 text-sm {{ request()->routeIs($pattern) ? 'bg-blue-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800' }}">
                    <i class="fa-solid {{ $icon }} w-5 text-center"></i>
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>
        <div class="px-5 py-4 border-t border-slate-700">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 bg-slate-700 hover:bg-slate-600 text-sm py-2 rounded">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col">
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold">@yield('heading', 'Dashboard')</h1>
            <a href="{{ route('home') }}" target="_blank" class="text-sm text-blue-600 hover:underline">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat Situs
            </a>
        </header>

        <main class="flex-1 p-6">
            @if (session('sukses'))
                <div class="mb-4 rounded border border-green-300 bg-green-50 text-green-800 px-4 py-3 text-sm">
                    <i class="fa-solid fa-circle-check mr-1"></i> {{ session('sukses') }}
                </div>
            @endif
            @if (session('gagal'))
                <div class="mb-4 rounded border border-red-300 bg-red-50 text-red-800 px-4 py-3 text-sm">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ session('gagal') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded border border-red-300 bg-red-50 text-red-800 px-4 py-3 text-sm">
                    <p class="font-semibold mb-1">Terdapat kesalahan pada input:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
