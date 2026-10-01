@props([
    'title',
    'subtitle' => null,
    'icon' => null,
])

<section {{ $attributes->merge(['class' => 'space-y-5']) }}>
    <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
        @if ($icon)
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                <i class="fa-solid {{ $icon }} text-sm"></i>
            </span>
        @endif
        <div>
            <h3 class="text-sm font-semibold text-slate-900">{{ $title }}</h3>
            @if ($subtitle)
                <p class="text-xs text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        {{ $slot }}
    </div>
</section>
