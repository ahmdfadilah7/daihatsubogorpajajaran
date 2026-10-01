<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Daihatsu Sahabat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen" x-data="{ sidebarOpen: false }">
<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 shrink-0 flex-col bg-slate-900 text-slate-100 transition-transform lg:static lg:flex lg:translate-x-0"
           :class="sidebarOpen ? 'flex translate-x-0' : 'hidden -translate-x-full lg:flex'">
        <div class="flex items-center gap-3 px-5 py-5 border-b border-slate-800">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600 text-white shadow-lg shadow-brand-600/30">
                <i class="fa-solid fa-car-side"></i>
            </span>
            <a href="{{ route('admin.dashboard') }}" class="leading-tight">
                <span class="block text-base font-bold">Daihatsu Admin</span>
                <span class="block text-xs text-slate-400">Panel Input Data</span>
            </a>
        </div>

        @php
            $navGroups = [
                'Umum' => [
                    ['admin.dashboard', 'Dashboard', 'fa-gauge', 'admin.dashboard'],
                ],
                'Konten' => [
                    ['admin.cars.index', 'Mobil', 'fa-car', 'admin.cars.*'],
                    ['admin.category-styles.index', 'Gaya Kategori', 'fa-palette', 'admin.category-styles.*'],
                    ['admin.hero-slides.index', 'Slide Hero', 'fa-images', 'admin.hero-slides.*'],
                    ['admin.corner-images.index', 'Gambar Pojok', 'fa-image', 'admin.corner-images.*'],
                ],
                'Interaktif' => [
                    ['admin.quiz-questions.index', 'Kuis', 'fa-circle-question', 'admin.quiz-questions.*'],
                    ['admin.wheel-prizes.index', 'Hadiah Roda', 'fa-gift', 'admin.wheel-prizes.*'],
                    ['admin.testimonials.index', 'Testimoni', 'fa-comment-dots', 'admin.testimonials.*'],
                ],
            ];
        @endphp

        <nav class="flex-1 overflow-y-auto py-4">
            @foreach ($navGroups as $group => $items)
                <p class="px-5 pt-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $group }}</p>
                <div class="space-y-1 px-3">
                    @foreach ($items as [$route, $label, $icon, $pattern])
                        <a href="{{ route($route) }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition {{ request()->routeIs($pattern) ? 'bg-brand-600 text-white font-semibold shadow-lg shadow-brand-600/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid {{ $icon }} w-5 text-center"></i>
                            <span>{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        <div class="space-y-2 border-t border-slate-800 px-3 py-4">
            <a href="{{ route('home') }}" target="_blank"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white">
                <i class="fa-solid fa-arrow-up-right-from-square w-5 text-center"></i>
                <span>Lihat Situs</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex w-full items-center gap-3 rounded-lg bg-slate-800 px-3 py-2.5 text-sm text-slate-200 transition hover:bg-brand-600 hover:text-white">
                    <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Mobile backdrop --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
         class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

    {{-- Main --}}
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-4 py-3 backdrop-blur sm:px-6">
            <div class="flex items-center gap-3">
                <button type="button" @click="sidebarOpen = !sidebarOpen"
                        class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 class="text-lg font-bold text-slate-900 sm:text-xl">@yield('heading', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-500">{{ Auth::user()->email }}</p>
                    </div>
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-600 text-sm font-semibold text-white">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar"
                                class="rounded-lg p-2 text-slate-500 transition hover:bg-brand-50 hover:text-brand-600">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                @endauth
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6">
            @if (session('sukses'))
                <div class="mb-4 flex items-start gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    <i class="fa-solid fa-circle-check mt-0.5"></i>
                    <span>{{ session('sukses') }}</span>
                </div>
            @endif
            @if (session('gagal'))
                <div class="mb-4 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                    <span>{{ session('gagal') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-semibold mb-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Terdapat kesalahan pada input:</p>
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
