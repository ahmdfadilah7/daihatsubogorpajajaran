@php
    // Props: $name, $label, $value
    $val = old($name, $value ?? '#0a5fd1');
    $val = preg_match('/^#[0-9A-Fa-f]{6}$/', $val) ? $val : '#0a5fd1';
@endphp
<div>
    <label class="block text-sm font-medium mb-1">{{ $label }}</label>
    <div class="flex items-center gap-2">
        <input type="color" value="{{ $val }}"
               oninput="this.nextElementSibling.value = this.value"
               class="h-10 w-14 rounded border border-slate-300 p-1">
        <input type="text" name="{{ $name }}" value="{{ old($name, $value ?? '') }}"
               oninput="this.previousElementSibling.value = /^#[0-9A-Fa-f]{6}$/.test(this.value) ? this.value : this.previousElementSibling.value"
               placeholder="#0a5fd1"
               class="flex-1 rounded border border-slate-300 px-3 py-2 text-sm">
    </div>
    @error($name)<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>
