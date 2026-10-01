<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Daihatsu Sahabat') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="min-h-screen grid lg:grid-cols-2">
            {{-- Brand panel (left) — hidden on small screens, degrades gracefully --}}
            <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-gradient-to-br from-brand-700 via-brand-600 to-brand-900 p-12 text-white">
                <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-white/10 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-black/20 blur-3xl"></div>

                <div class="relative flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/30">
                        <i class="fa-solid fa-car-side text-xl"></i>
                    </span>
                    <div class="leading-tight">
                        <p class="text-lg font-semibold tracking-wide">Daihatsu Sahabat</p>
                        <p class="text-xs text-white/70">Panel Input Data</p>
                    </div>
                </div>

                <div class="relative max-w-md">
                    <h1 class="text-4xl font-bold leading-tight">Kelola konten situs dengan mudah.</h1>
                    <p class="mt-4 text-base text-white/80">
                        Satu dashboard untuk mengatur mobil, kuis, hadiah, galeri, dan testimoni
                        Daihatsu Sahabat. Cepat, rapi, dan selalu terkini.
                    </p>

                    <ul class="mt-8 space-y-3 text-sm text-white/90">
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-circle-check text-white/80"></i> Input data terpusat
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-circle-check text-white/80"></i> Statistik konten real-time
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-circle-check text-white/80"></i> Aman dengan autentikasi
                        </li>
                    </ul>
                </div>

                <p class="relative text-xs text-white/60">&copy; {{ date('Y') }} Daihatsu Sahabat. Seluruh hak cipta dilindungi.</p>
            </div>

            {{-- Form column (right) --}}
            <div class="flex min-h-screen items-center justify-center bg-slate-50 p-6 sm:p-10">
                <div class="w-full max-w-md">
                    {{-- Compact brand mark for mobile (brand panel is hidden) --}}
                    <div class="mb-8 flex items-center justify-center gap-3 lg:hidden">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 text-white shadow-lg shadow-brand-600/30">
                            <i class="fa-solid fa-car-side text-xl"></i>
                        </span>
                        <div class="leading-tight">
                            <p class="text-lg font-semibold text-slate-900">Daihatsu Sahabat</p>
                            <p class="text-xs text-slate-500">Panel Input Data</p>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white p-8 shadow-xl shadow-slate-200/60 ring-1 ring-slate-100">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
