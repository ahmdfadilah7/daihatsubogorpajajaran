@extends('layouts.admin')

@section('title', 'Profil')
@section('heading', 'Profil Saya')

@section('content')
    @php
        $inputClass = 'w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500';
        $errClass = 'border-red-400 focus:border-red-500 focus:ring-red-500';
    @endphp

    <x-admin.form-shell
        title="Profil Saya"
        description="Perbarui informasi akun dan kata sandi Anda."
        :back="route('admin.dashboard')"
        back-label="Kembali ke dashboard"
        icon="fa-id-card">

        {{-- Informasi Profil --}}
        <form action="{{ route('admin.profile.update') }}" method="POST">
            @csrf
            @method('PATCH')
            <x-admin.form-section title="Informasi Profil" subtitle="Nama dan email akun Anda" icon="fa-user">
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
                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="md:col-span-2">
                        <p class="flex items-center gap-1 text-xs text-amber-600"><i class="fa-solid fa-triangle-exclamation"></i>Alamat email Anda belum terverifikasi.</p>
                    </div>
                @endif
            </x-admin.form-section>
            @include('admin.partials.form-actions', ['cancel' => route('admin.dashboard')])
        </form>

        {{-- Ubah Kata Sandi --}}
        <div class="mt-10 border-t border-slate-100 pt-8">
            @if (session('status') === 'password-updated')
                <div class="mb-5 flex items-start gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    <i class="fa-solid fa-circle-check mt-0.5"></i>
                    <span>Kata sandi berhasil diperbarui.</span>
                </div>
            @endif
            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                @method('PUT')
                <x-admin.form-section title="Ubah Kata Sandi" subtitle="Kosongkan jika tidak ingin mengubah" icon="fa-lock">
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" autocomplete="current-password" class="{{ $inputClass }} @if ($errors->updatePassword->has('current_password')) {{ $errClass }} @endif">
                        @foreach ($errors->updatePassword->get('current_password') as $message)
                            <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>
                        @endforeach
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Kata Sandi Baru</label>
                        <input type="password" name="password" autocomplete="new-password" class="{{ $inputClass }} @if ($errors->updatePassword->has('password')) {{ $errClass }} @endif">
                        @foreach ($errors->updatePassword->get('password') as $message)
                            <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>
                        @endforeach
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password" class="{{ $inputClass }}">
                    </div>
                </x-admin.form-section>
                <div class="mt-8 flex justify-end border-t border-slate-100 pt-6">
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                        <i class="fa-solid fa-key"></i>
                        Perbarui Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </x-admin.form-shell>
@endsection
