<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Lupa kata sandi?</h2>
        <p class="mt-1 text-sm text-slate-500">
            Masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="mb-1.5 text-slate-700" />
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i class="fa-solid fa-envelope"></i>
                </span>
                <x-text-input id="email" class="block w-full rounded-lg border-slate-300 pl-10 focus:border-brand-500 focus:ring-brand-500" type="email" name="email" :value="old('email')" required autofocus />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
            {{ __('Email Password Reset Link') }}
        </button>
    </form>
</x-guest-layout>
