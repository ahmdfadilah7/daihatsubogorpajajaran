@php
    $inputClass = 'w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500';
    $errClass = 'border-red-400 focus:border-red-500 focus:ring-red-500';
@endphp

<div class="space-y-5">
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Nama <span class="text-brand-600">*</span></label>
            <input type="text" name="name" value="{{ old('name', $heroSlide->name) }}" class="{{ $inputClass }} @error('name') {{ $errClass }} @enderror">
            @error('name')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tag <span class="text-brand-600">*</span></label>
            <input type="text" name="tag" value="{{ old('tag', $heroSlide->tag) }}" class="{{ $inputClass }} @error('tag') {{ $errClass }} @enderror">
            @error('tag')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Deskripsi <span class="text-brand-600">*</span></label>
        <textarea name="description" rows="3" class="{{ $inputClass }} @error('description') {{ $errClass }} @enderror">{{ old('description', $heroSlide->description) }}</textarea>
        @error('description')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Harga (teks tampilan) <span class="text-brand-600">*</span></label>
        <input type="text" name="price" value="{{ old('price', $heroSlide->price) }}" placeholder="Rp 219 Jt" class="{{ $inputClass }} @error('price') {{ $errClass }} @enderror">
        <p class="mt-1 text-xs text-slate-400">Ditampilkan apa adanya pada slide, mis. "Rp 219 Jt".</p>
        @error('price')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>
    @include('admin.partials.image-input', ['name' => 'img', 'label' => 'Gambar', 'value' => $heroSlide->img])
</div>

@include('admin.partials.form-actions', ['cancel' => route('admin.hero-slides.index')])
