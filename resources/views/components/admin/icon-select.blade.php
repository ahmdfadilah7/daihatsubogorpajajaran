@props([
    'name' => 'icon',
    'value' => '',
    'required' => false,
])

@php
    $iconList = config('icons.list', []);
    $selected = (string) $value;
    $selectClass = 'flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500';
@endphp

<div x-data="{ icon: @js($selected) }" class="flex items-center gap-2">
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-700">
        <i class="fa-solid" :class="icon"></i>
    </span>
    <select name="{{ $name }}" x-model="icon" @if ($required) required @endif
            {{ $attributes->merge(['class' => $selectClass . ($errors->has($name) ? ' border-red-400 focus:border-red-500 focus:ring-red-500' : '')]) }}>
        @unless ($required)
            <option value="">— pilih ikon —</option>
        @endunless
        @foreach ($iconList as $class => $label)
            <option value="{{ $class }}" @selected($selected === $class)>{{ $label }} ({{ $class }})</option>
        @endforeach
    </select>
</div>
