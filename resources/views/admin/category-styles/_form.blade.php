@php
    $inputClass = 'w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500';
    $errClass = 'border-red-400 focus:border-red-500 focus:ring-red-500';
    // On edit the category key is the relation anchor and must not be renamed.
    $isEdit = $categoryStyle->exists;
@endphp

<div class="space-y-5">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">
            Kategori @unless($isEdit)<span class="text-brand-600">*</span>@endunless
        </label>
        @if ($isEdit)
            {{-- Read-only: kategori adalah kunci relasi dan tidak dapat diubah. --}}
            <input type="text" value="{{ $categoryStyle->category }}" disabled readonly
                   class="w-full cursor-not-allowed rounded-lg border-slate-200 bg-slate-100 text-sm text-slate-500 shadow-sm">
            <p class="mt-1.5 flex items-center gap-1 text-xs text-slate-400">
                <i class="fa-solid fa-lock"></i>Kategori tidak dapat diubah setelah dibuat.
            </p>
        @else
            <input type="text" name="category" value="{{ old('category', $categoryStyle->category) }}"
                   placeholder="LCGC / MPV / SUV / Niaga"
                   class="{{ $inputClass }} @error('category') {{ $errClass }} @enderror">
            @error('category')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        @endif
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Label <span class="text-brand-600">*</span></label>
        <input type="text" name="label" value="{{ old('label', $categoryStyle->label) }}" class="{{ $inputClass }} @error('label') {{ $errClass }} @enderror">
        @error('label')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>
    @include('admin.partials.color-input', ['name' => 'bg', 'label' => 'Warna Latar', 'value' => $categoryStyle->bg])
</div>

@include('admin.partials.form-actions', ['cancel' => route('admin.category-styles.index')])
