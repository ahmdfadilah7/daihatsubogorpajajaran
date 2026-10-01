@php
    // Props: $name (string field: img or src), $label, $value (current string)
    $current = old($name, $value ?? '');
@endphp
<div class="space-y-2">
    <label class="block text-sm font-medium">{{ $label }}</label>
    @if (!empty($value))
        <div class="text-xs text-slate-500">
            Gambar saat ini:
            <a href="{{ \Illuminate\Support\Str::startsWith($value, ['http://','https://']) ? $value : asset($value) }}" target="_blank" class="text-blue-600 hover:underline break-all">{{ $value }}</a>
        </div>
    @endif
    <input type="text" name="{{ $name }}" value="{{ $current }}"
           placeholder="URL (https://...) atau path (img/.. , storage/..)"
           class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
    @error($name)<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror

    <div>
        <label class="block text-xs text-slate-500 mb-1">atau unggah berkas</label>
        <input type="file" name="image" accept="image/*"
               class="w-full text-sm text-slate-600 file:mr-3 file:rounded file:border-0 file:bg-slate-200 file:px-3 file:py-1.5 file:text-sm">
        @error('image')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>
