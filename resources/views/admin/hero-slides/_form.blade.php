<div class="space-y-5">
    <div>
        <label class="block text-sm font-medium mb-1">Nama</label>
        <input type="text" name="name" value="{{ old('name', $heroSlide->name) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Deskripsi</label>
        <textarea name="description" rows="2" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">{{ old('description', $heroSlide->description) }}</textarea>
        @error('description')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Tag</label>
        <input type="text" name="tag" value="{{ old('tag', $heroSlide->tag) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('tag')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Harga (teks tampilan)</label>
        <input type="text" name="price" value="{{ old('price', $heroSlide->price) }}" placeholder="Rp 219 Jt" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('price')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    @include('admin.partials.image-input', ['name' => 'img', 'label' => 'Gambar', 'value' => $heroSlide->img])
</div>

<div class="mt-6 flex gap-2">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-5 py-2 rounded">Simpan</button>
    <a href="{{ route('admin.hero-slides.index') }}" class="bg-slate-200 hover:bg-slate-300 text-sm px-5 py-2 rounded">Batal</a>
</div>
