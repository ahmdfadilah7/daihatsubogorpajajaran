@php
    $categories = ['LCGC', 'MPV', 'SUV', 'Niaga'];
    $transmissions = ['CVT', 'Manual', 'Otomatis'];
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-medium mb-1">Model</label>
        <input type="text" name="model" value="{{ old('model', $car->model) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('model')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Tipe</label>
        <input type="text" name="type" value="{{ old('type', $car->type) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('type')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Kategori</label>
        <select name="category" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            @foreach ($categories as $c)
                <option value="{{ $c }}" @selected(old('category', $car->category) === $c)>{{ $c }}</option>
            @endforeach
        </select>
        @error('category')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Transmisi</label>
        <select name="transmission" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            @foreach ($transmissions as $t)
                <option value="{{ $t }}" @selected(old('transmission', $car->transmission) === $t)>{{ $t }}</option>
            @endforeach
        </select>
        @error('transmission')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Tahun</label>
        <input type="number" name="year" value="{{ old('year', $car->year) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('year')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Harga (Rupiah)</label>
        <input type="number" name="price" value="{{ old('price', $car->price) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('price')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Bahan Bakar</label>
        <input type="text" name="fuel" value="{{ old('fuel', $car->fuel) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('fuel')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Jumlah Kursi</label>
        <input type="number" name="seats" value="{{ old('seats', $car->seats) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('seats')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Badge (opsional)</label>
        <input type="text" name="badge" value="{{ old('badge', $car->badge) }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error('badge')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div></div>
    @include('admin.partials.color-input', ['name' => 'accent1', 'label' => 'Warna Aksen 1', 'value' => $car->accent1])
    @include('admin.partials.color-input', ['name' => 'accent2', 'label' => 'Warna Aksen 2', 'value' => $car->accent2])
    <div class="md:col-span-2">
        @include('admin.partials.image-input', ['name' => 'img', 'label' => 'Gambar', 'value' => $car->img])
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-5 py-2 rounded">Simpan</button>
    <a href="{{ route('admin.cars.index') }}" class="bg-slate-200 hover:bg-slate-300 text-sm px-5 py-2 rounded">Batal</a>
</div>
