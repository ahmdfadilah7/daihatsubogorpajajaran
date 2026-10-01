@php
    $inputClass = 'w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500';
    $errClass = 'border-red-400 focus:border-red-500 focus:ring-red-500';
@endphp

<div class="space-y-5">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Teks <span class="text-brand-600">*</span></label>
        <input type="text" name="text" value="{{ old('text', $item->text) }}" placeholder="mis. Irit BBM" class="{{ $inputClass }} @error('text') {{ $errClass }} @enderror">
        <p class="mt-1 text-xs text-slate-400">Teks keunggulan yang tampil pada strip berjalan di halaman depan.</p>
        @error('text')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>

    <div x-data="{ icon: @js(old('icon', $item->icon ?? '')) }">
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Ikon <span class="text-brand-600">*</span></label>
        <div class="flex items-center gap-2">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-700">
                <i class="fa-solid" :class="icon"></i>
            </span>
            <input type="text" name="icon" x-model="icon" placeholder="fa-gas-pump" class="flex-1 {{ $inputClass }} @error('icon') {{ $errClass }} @enderror">
        </div>
        <p class="mt-1 flex items-center gap-1 text-xs text-slate-400"><i class="fa-solid fa-circle-info"></i>Kelas Font Awesome, mis. fa-gas-pump</p>
        @error('icon')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        @include('admin.partials.color-input', ['name' => 'color', 'label' => 'Warna', 'value' => $item->color])
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Urutan</label>
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $item->sort_order) }}" class="{{ $inputClass }} @error('sort_order') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Angka lebih kecil tampil lebih dulu.</p>
            @error('sort_order')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </div>
</div>

@include('admin.partials.form-actions', ['cancel' => route('admin.marquee-items.index')])
