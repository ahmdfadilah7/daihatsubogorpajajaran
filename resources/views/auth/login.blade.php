<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900">Selamat datang kembali</h2>
        <p class="mt-1 text-sm text-slate-500">Masuk ke panel admin Daihatsu Sahabat untuk mengelola data.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="mb-1.5 text-slate-700" />
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i class="fa-solid fa-envelope"></i>
                </span>
                <x-text-input id="email"
                              class="block w-full rounded-lg border-slate-300 pl-10 focus:border-brand-500 focus:ring-brand-500"
                              type="email"
                              name="email"
                              :value="old('email')"
                              required
                              autofocus
                              autocomplete="username"
                              placeholder="admin@daihatsu.test" />
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
                <x-text-input id="password"
                              class="block w-full rounded-lg border-slate-300 pl-10 focus:border-brand-500 focus:ring-brand-500"
                              type="password"
                              name="password"
                              required
                              autocomplete="current-password"
                              placeholder="••••••••" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me + Forgot password -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                       class="rounded border-slate-300 text-brand-600 shadow-sm focus:ring-brand-500"
                       name="remember">
                <span class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-brand-600 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 rounded"
                   href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
            <i class="fa-solid fa-right-to-bracket"></i>
            {{ __('Log in') }}
        </button>
    </form>
</x-guest-layout>
