@props([
    'title',
    'description' => null,
    'back',
    'backLabel' => 'Kembali',
    'icon' => 'fa-pen-to-square',
    'maxWidth' => 'max-w-4xl',
])

<div class="mx-auto {{ $maxWidth }}">
    {{-- Page header: title + description + back link --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-3">
            <span class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-lg shadow-brand-600/30 sm:flex">
                <i class="fa-solid {{ $icon }}"></i>
            </span>
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-900">{{ $title }}</h2>
                @if ($description)
                    <p class="mt-0.5 text-sm text-slate-500">{{ $description }}</p>
                @endif
            </div>
        </div>
        <a href="{{ $back }}"
           class="inline-flex items-center gap-2 self-start rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-brand-600">
            <i class="fa-solid fa-arrow-left"></i>
            {{ $backLabel }}
        </a>
    </div>

    {{-- Card --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        {{ $slot }}
    </div>
</div>
