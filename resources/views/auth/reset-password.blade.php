<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Atur ulang kata sandi</h2>
        <p class="mt-1 text-sm text-slate-500">Pilih kata sandi baru untuk akun Anda.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="mb-1.5 text-slate-700" />
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i class="fa-solid fa-envelope"></i>
                </span>
                <x-text-input id="email" class="block w-full rounded-lg border-slate-300 pl-10 focus:border-brand-500 focus:ring-brand-500" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="mb-1.5 text-slate-700" />
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i class="fa-solid fa-lock"></i>
                </span>
                <x-text-input id="password" class="block w-full rounded-lg border-slate-300 pl-10 focus:border-brand-500 focus:ring-brand-500" type="password" name="password" required autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="mb-1.5 text-slate-700" />
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i class="fa-solid fa-lock"></i>
                </span>
                <x-text-input id="password_confirmation" class="block w-full rounded-lg border-slate-300 pl-10 focus:border-brand-500 focus:ring-brand-500" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
            {{ __('Reset Password') }}
        </button>
    </form>
</x-guest-layout>
