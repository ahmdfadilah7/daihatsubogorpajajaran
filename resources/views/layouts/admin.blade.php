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
                ['admin.marquee-items.index', 'Teks Berjalan', 'fa-bullhorn', 'admin.marquee-items.*'],
            ],
            'Interaktif' => [
                ['admin.quiz-questions.index', 'Kuis', 'fa-circle-question', 'admin.quiz-questions.*'],
                ['admin.wheel-prizes.index', 'Hadiah Roda', 'fa-gift', 'admin.wheel-prizes.*'],
                ['admin.testimonials.index', 'Testimoni', 'fa-comment-dots', 'admin.testimonials.*'],
            ],
            'Sistem' => [
                ['admin.users.index', 'Pengguna', 'fa-users', 'admin.users.*'],
                ['admin.profile.edit', 'Profil', 'fa-id-card', 'admin.profile.*'],
                ['admin.settings.edit', 'Pengaturan Website', 'fa-gear', 'admin.settings.*'],
            ],
        ];

        // Derive a human-readable section label for the header breadcrumb from
        // the active route, without changing any route or nav wiring.
        $activeSection = 'Dashboard';
        foreach ($navGroups as $groupItems) {
            foreach ($groupItems as [$navRoute, $navLabel, $navIcon, $navPattern]) {
                if (request()->routeIs($navPattern)) {
                    $activeSection = $navLabel;
                }
            }
        }
    @endphp

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 shrink-0 flex-col bg-gradient-to-b from-slate-900 to-slate-950 text-slate-100 shadow-xl transition-transform lg:sticky lg:top-0 lg:h-screen lg:flex lg:translate-x-0"
           :class="sidebarOpen ? 'flex translate-x-0' : 'hidden -translate-x-full lg:flex'">
        <div class="flex items-center gap-3 border-b border-white/10 px-5 py-5">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-lg shadow-brand-600/40">
                <i class="fa-solid fa-car-side text-lg"></i>
            </span>
            <a href="{{ route('admin.dashboard') }}" class="leading-tight">
                <span class="block text-base font-bold tracking-tight">Daihatsu Admin</span>
                <span class="block text-xs text-slate-400">Panel Input Data</span>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto py-4">
            @foreach ($navGroups as $group => $items)
                <p class="px-5 pt-4 pb-2 text-[11px] font-semibold uppercase tracking-widest text-slate-500">{{ $group }}</p>
                <div class="space-y-1 px-3">
                    @foreach ($items as [$route, $label, $icon, $pattern])
                        @php $isActive = request()->routeIs($pattern); @endphp
                        <a href="{{ route($route) }}"
                           @if ($isActive) aria-current="page" @endif
                           class="group relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition {{ $isActive ? 'bg-gradient-to-r from-brand-600 to-brand-500 font-semibold text-white shadow-lg shadow-brand-600/30' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                            @if ($isActive)
                                <span class="absolute inset-y-1.5 left-0 w-1 rounded-r-full bg-white/80"></span>
                            @endif
                            <i class="fa-solid {{ $icon }} w-5 text-center {{ $isActive ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                            <span>{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        <div class="space-y-2 border-t border-white/10 px-3 py-4">
            <a href="{{ route('home') }}" target="_blank"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white">
                <i class="fa-solid fa-arrow-up-right-from-square w-5 text-center"></i>
                <span>Lihat Situs</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex w-full items-center gap-3 rounded-lg bg-white/5 px-3 py-2.5 text-sm text-slate-200 transition hover:bg-brand-600 hover:text-white">
                    <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Mobile backdrop --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak x-transition.opacity
         class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

    {{-- Main --}}
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-slate-200 bg-white/90 px-4 py-3 shadow-sm backdrop-blur sm:px-6">
            <div class="flex min-w-0 items-center gap-3">
                <button type="button" @click="sidebarOpen = !sidebarOpen"
                        class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 lg:hidden">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="min-w-0">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium uppercase tracking-wide text-slate-400">
                        <span>Dashboard</span>
                        <i class="fa-solid fa-chevron-right text-[8px]"></i>
                        <span class="text-brand-600">{{ $activeSection }}</span>
                    </p>
                    <h1 class="truncate text-lg font-bold text-slate-900 sm:text-xl">@yield('heading', 'Dashboard')</h1>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('home') }}" target="_blank"
                   class="hidden items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-brand-600 sm:inline-flex">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>Lihat Situs</span>
                </a>

                @auth
                    <div class="relative" x-data="{ userMenu: false }">
                        <button type="button" @click="userMenu = !userMenu"
                                class="flex items-center gap-2 rounded-lg p-1 pr-2 text-left transition hover:bg-slate-100"
                                :aria-expanded="userMenu" aria-haspopup="true">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-sm font-semibold text-white shadow-sm">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <span class="hidden text-right sm:block">
                                <span class="block text-sm font-semibold leading-tight text-slate-900">{{ Auth::user()->name }}</span>
                                <span class="block text-xs leading-tight text-slate-500">{{ Auth::user()->email }}</span>
                            </span>
                            <i class="fa-solid fa-chevron-down hidden text-xs text-slate-400 sm:inline"></i>
                        </button>

                        <div x-show="userMenu" x-cloak x-transition
                             @click.outside="userMenu = false"
                             @keydown.escape.window="userMenu = false"
                             class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-xl ring-1 ring-slate-900/5">
                            <div class="border-b border-slate-100 px-4 py-3">
                                <p class="text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('admin.profile.edit') }}"
                               class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-brand-600">
                                <i class="fa-solid fa-id-card w-4 text-center"></i>
                                <span>Profil</span>
                            </a>
                            <a href="{{ route('home') }}" target="_blank"
                               class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-brand-600">
                                <i class="fa-solid fa-arrow-up-right-from-square w-4 text-center"></i>
                                <span>Lihat Situs</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-red-50 hover:text-red-600">
                                    <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Direct logout control for small screens (dropdown label hidden on mobile) --}}
                    <form method="POST" action="{{ route('logout') }}" class="sm:hidden">
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

@include('admin.partials.confirm-modal')

@stack('scripts')
</body>
</html>
