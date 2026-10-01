@php
    // Props: $name, $label, $value
    $val = old($name, $value ?? '#0a5fd1');
    $val = preg_match('/^#[0-9A-Fa-f]{6}$/', $val) ? $val : '#0a5fd1';
    // Raw stored value for the text input (may be empty on create).
    $raw = old($name, $value ?? '');
@endphp
<div x-data="{ color: @js($raw), swatch: @js($val) }"
     x-init="$watch('color', v => { if (/^#[0-9A-Fa-f]{6}$/.test(v)) swatch = v })">
    <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ $label }}</label>
    <div class="flex items-center gap-2">
        {{-- Live swatch preview --}}
        <span class="h-10 w-10 shrink-0 rounded-lg border border-slate-200 shadow-inner"
              :style="`background:${swatch}`"></span>
        {{-- Native visual picker (not submitted; keeps the hex text input in sync) --}}
        <input type="color" x-model="swatch" @input="color = swatch"
               aria-label="Pilih warna {{ $label }}"
               class="h-10 w-12 shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
        {{-- Submitted hex value --}}
        <input type="text" name="{{ $name }}" x-model="color"
               placeholder="#0a5fd1"
               class="flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 @error($name) border-red-400 @enderror">
    </div>
    @error($name)
        <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600">
            <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
        </p>
    @enderror
</div>
