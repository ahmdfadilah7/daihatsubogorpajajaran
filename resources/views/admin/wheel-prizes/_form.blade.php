@php
    $inputClass = 'w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500';
    $errClass = 'border-red-400 focus:border-red-500 focus:ring-red-500';
@endphp

<div class="space-y-5">
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Label <span class="text-brand-600">*</span></label>
            <input type="text" name="label" value="{{ old('label', $prize->label) }}" class="{{ $inputClass }} @error('label') {{ $errClass }} @enderror">
            @error('label')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Bobot (peluang) <span class="text-brand-600">*</span></label>
            <input type="number" name="weight" value="{{ old('weight', $prize->weight) }}" class="{{ $inputClass }} @error('weight') {{ $errClass }} @enderror">
            <p class="mt-1 text-xs text-slate-400">Semakin besar bobot, semakin sering hadiah ini muncul.</p>
            @error('weight')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Teks Singkat <span class="text-brand-600">*</span></label>
        <textarea name="short" rows="2" class="{{ $inputClass }} @error('short') {{ $errClass }} @enderror">{{ old('short', $prize->short) }}</textarea>
        <p class="mt-1 flex items-center gap-1 text-xs text-slate-400"><i class="fa-solid fa-circle-info"></i>Gunakan baris baru untuk memisahkan teks pada roda.</p>
        @error('short')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
    </div>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        @include('admin.partials.color-input', ['name' => 'color', 'label' => 'Warna', 'value' => $prize->color])
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Pesan <span class="text-brand-600">*</span></label>
            <input type="text" name="msg" value="{{ old('msg', $prize->msg) }}" placeholder="Pesan saat hadiah menang" class="{{ $inputClass }} @error('msg') {{ $errClass }} @enderror">
            @error('msg')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </div>
</div>

@include('admin.partials.form-actions', ['cancel' => route('admin.wheel-prizes.index')])
