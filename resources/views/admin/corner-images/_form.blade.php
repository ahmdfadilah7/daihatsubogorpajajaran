@php
    $inputClass = 'w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500';
    $errClass = 'border-red-400 focus:border-red-500 focus:ring-red-500';
@endphp

<div class="space-y-5">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Teks Alternatif <span class="text-brand-600">*</span></label>
        <input type="text" name="alt" value="{{ old('alt', $cornerImage->alt) }}" placeholder="Deskripsi singkat gambar" class="{{ $inputClass }} @error('alt') {{ $errClass }} @enderror">
        <p class="mt-1 text-xs text-slate-400">Teks pengganti untuk aksesibilitas dan SEO.</p>
        @error('alt')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>
    @include('admin.partials.image-input', ['name' => 'src', 'label' => 'Gambar', 'value' => $cornerImage->src])
</div>

@include('admin.partials.form-actions', ['cancel' => route('admin.corner-images.index')])
