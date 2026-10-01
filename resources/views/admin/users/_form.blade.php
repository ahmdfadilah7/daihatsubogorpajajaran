@php
    $inputClass = 'w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500';
    $errClass = 'border-red-400 focus:border-red-500 focus:ring-red-500';
    $isEdit = $user->exists;
@endphp

<div class="space-y-8">
    {{-- Informasi Pengguna --}}
    <x-admin.form-section title="Informasi Pengguna" subtitle="Nama dan email akun" icon="fa-user">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Nama <span class="text-brand-600">*</span></label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="{{ $inputClass }} @error('name') {{ $errClass }} @enderror">
            @error('name')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Email <span class="text-brand-600">*</span></label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="{{ $inputClass }} @error('email') {{ $errClass }} @enderror">
            @error('email')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>

    {{-- Kata Sandi --}}
    <x-admin.form-section title="Kata Sandi" subtitle="{{ $isEdit ? 'Kosongkan jika tidak ingin mengubah kata sandi' : 'Minimal 8 karakter' }}" icon="fa-lock">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                Kata Sandi @if (! $isEdit)<span class="text-brand-600">*</span>@endif
            </label>
            <input type="password" name="password" autocomplete="new-password" class="{{ $inputClass }} @error('password') {{ $errClass }} @enderror">
            @if ($isEdit)
                <p class="mt-1.5 text-xs text-slate-400">Kosongkan jika tidak ingin mengubah kata sandi.</p>
            @endif
            @error('password')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                Konfirmasi Kata Sandi @if (! $isEdit)<span class="text-brand-600">*</span>@endif
            </label>
            <input type="password" name="password_confirmation" autocomplete="new-password" class="{{ $inputClass }} @error('password_confirmation') {{ $errClass }} @enderror">
            @error('password_confirmation')<p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
        </div>
    </x-admin.form-section>
</div>

@include('admin.partials.form-actions', ['cancel' => route('admin.users.index')])
