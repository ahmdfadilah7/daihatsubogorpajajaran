@php
    $categories = ['LCGC', 'MPV', 'SUV', 'Niaga'];
    $transmissions = ['CVT', 'Manual', 'Otomatis'];
    $inputClass = 'w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500';
    $errClass = 'border-red-400 focus:border-red-500 focus:ring-red-500';
@endphp

<div class="space-y-8">
    {{-- Informasi Umum --}}
    <x-admin.form-section title="Informasi Umum" subtitle="Identitas dan kategori mobil" icon="fa-circle-info">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Model <span class="text-brand-600">*</span></label>
            <input type="text" name="model" value="{{ old('model', $car->model) }}" class="{{ $inputClass }} @error('model') {{ $errClass }} @enderror">
            @error('model')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tipe <span class="text-brand-600">*</span></label>
            <input type="text" name="type" value="{{ old('type', $car->type) }}" class="{{ $inputClass }} @error('type') {{ $errClass }} @enderror">
            @error('type')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Kategori <span class="text-brand-600">*</span></label>
            <select name="category" class="{{ $inputClass }} @error('category') {{ $errClass }} @enderror">
                @foreach ($categories as $c)
                    <option value="{{ $c }}" @selected(old('category', $car->category) === $c)>{{ $c }}</option>
                @endforeach
            </select>
            @error('category')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Badge <span class="text-slate-400">(opsional)</span></label>
            <input type="text" name="badge" value="{{ old('badge', $car->badge) }}" placeholder="mis. Terlaris" class="{{ $inputClass }} @error('badge') {{ $errClass }} @enderror">
            @error('badge')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>

    {{-- Spesifikasi --}}
    <x-admin.form-section title="Spesifikasi" subtitle="Detail teknis dan harga" icon="fa-gears">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Transmisi <span class="text-brand-600">*</span></label>
            <select name="transmission" class="{{ $inputClass }} @error('transmission') {{ $errClass }} @enderror">
                @foreach ($transmissions as $t)
                    <option value="{{ $t }}" @selected(old('transmission', $car->transmission) === $t)>{{ $t }}</option>
                @endforeach
            </select>
            @error('transmission')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Bahan Bakar <span class="text-brand-600">*</span></label>
            <input type="text" name="fuel" value="{{ old('fuel', $car->fuel) }}" placeholder="mis. Bensin" class="{{ $inputClass }} @error('fuel') {{ $errClass }} @enderror">
            @error('fuel')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tahun <span class="text-brand-600">*</span></label>
            <input type="number" name="year" value="{{ old('year', $car->year) }}" class="{{ $inputClass }} @error('year') {{ $errClass }} @enderror">
            @error('year')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Jumlah Kursi <span class="text-brand-600">*</span></label>
            <input type="number" name="seats" value="{{ old('seats', $car->seats) }}" class="{{ $inputClass }} @error('seats') {{ $errClass }} @enderror">
            @error('seats')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Harga (Rupiah) <span class="text-brand-600">*</span></label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-medium text-slate-400">Rp</span>
                <input type="number" name="price" value="{{ old('price', $car->price) }}" class="{{ $inputClass }} pl-10 @error('price') {{ $errClass }} @enderror">
            </div>
            @error('price')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>

    {{-- Tampilan & Media --}}
    <x-admin.form-section title="Tampilan & Media" subtitle="Warna aksen dan gambar mobil" icon="fa-palette">
        @include('admin.partials.color-input', ['name' => 'accent1', 'label' => 'Warna Aksen 1', 'value' => $car->accent1])
        @include('admin.partials.color-input', ['name' => 'accent2', 'label' => 'Warna Aksen 2', 'value' => $car->accent2])
        <div class="md:col-span-2">
            @include('admin.partials.image-input', ['name' => 'img', 'label' => 'Gambar', 'value' => $car->img])
        </div>
    </x-admin.form-section>
</div>

@include('admin.partials.form-actions', ['cancel' => route('admin.cars.index')])
