<div class="space-y-5">
    <div>
        <label class="block text-sm font-medium mb-1">Teks Alternatif</label>
        <input type="text" name="alt" value="{{ old('alt', $cornerImage->alt) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('alt')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    @include('admin.partials.image-input', ['name' => 'src', 'label' => 'Gambar', 'value' => $cornerImage->src])
</div>

<div class="mt-6 flex gap-2">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-5 py-2 rounded">Simpan</button>
    <a href="{{ route('admin.corner-images.index') }}" class="bg-slate-200 hover:bg-slate-300 text-sm px-5 py-2 rounded">Batal</a>
</div>
