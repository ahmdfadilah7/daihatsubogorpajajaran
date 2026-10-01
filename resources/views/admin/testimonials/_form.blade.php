<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-medium mb-1">Nama</label>
        <input type="text" name="name" value="{{ old('name', $testimonial->name) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Kota</label>
        <input type="text" name="city" value="{{ old('city', $testimonial->city) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('city')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Mobil</label>
        <input type="text" name="car" value="{{ old('car', $testimonial->car) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('car')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Rating (1–5)</label>
        <input type="number" name="rating" min="1" max="5" value="{{ old('rating', $testimonial->rating) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('rating')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    @include('admin.partials.color-input', ['name' => 'color', 'label' => 'Warna', 'value' => $testimonial->color])
    <div></div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">Teks Testimoni</label>
        <textarea name="text" rows="3" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">{{ old('text', $testimonial->text) }}</textarea>
        @error('text')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        @include('admin.partials.image-input', ['name' => 'img', 'label' => 'Foto', 'value' => $testimonial->img])
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-5 py-2 rounded">Simpan</button>
    <a href="{{ route('admin.testimonials.index') }}" class="bg-slate-200 hover:bg-slate-300 text-sm px-5 py-2 rounded">Batal</a>
</div>
