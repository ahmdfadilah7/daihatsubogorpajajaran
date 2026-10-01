<div class="space-y-5">
    <div>
        <label class="block text-sm font-medium mb-1">Label</label>
        <input type="text" name="label" value="{{ old('label', $prize->label) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('label')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Teks Singkat</label>
        <textarea name="short" rows="2" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">{{ old('short', $prize->short) }}</textarea>
        <p class="text-xs text-slate-400 mt-1">Gunakan baris baru untuk memisahkan teks pada roda.</p>
        @error('short')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    @include('admin.partials.color-input', ['name' => 'color', 'label' => 'Warna', 'value' => $prize->color])
    <div>
        <label class="block text-sm font-medium mb-1">Bobot (peluang)</label>
        <input type="number" name="weight" value="{{ old('weight', $prize->weight) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('weight')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Pesan</label>
        <input type="text" name="msg" value="{{ old('msg', $prize->msg) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('msg')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-5 py-2 rounded">Simpan</button>
    <a href="{{ route('admin.wheel-prizes.index') }}" class="bg-slate-200 hover:bg-slate-300 text-sm px-5 py-2 rounded">Batal</a>
</div>
