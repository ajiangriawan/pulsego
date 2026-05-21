<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirectIntended(default: route('home', absolute: false), navigate: true);
    }
}; ?>

<div>
    {{-- Header --}}
    <div class="px-8 pt-8 pb-6" style="background: linear-gradient(135deg, #1E6B2A, #3A9E3F);">
        <h1 class="text-2xl font-extrabold text-white mb-1">Selamat Datang! 👋</h1>
        <p class="text-green-200 text-sm font-medium">Masuk untuk mulai booking lapangan</p>
    </div>

    <div class="px-8 py-7">

        {{-- Session Status --}}
        <x-auth-session-status class="mb-5" :status="session('status')" />

        <form wire:submit="login" class="space-y-5">

            {{-- Email --}}
            <div>
                <label for="email" class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                    Alamat Email
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none">
                        <svg class="w-4.5 h-4.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input wire:model="form.email"
                           id="email" type="email" name="email"
                           required autofocus autocomplete="username"
                           placeholder="nama@email.com"
                           class="w-full pl-10 pr-4 py-3 rounded-xl border-2 border-gray-200 text-sm font-medium text-gray-800 placeholder-gray-400 focus:border-green-500 focus:ring-0 transition-colors outline-none">
                </div>
                <x-input-error :messages="$errors->get('form.email')" class="mt-1.5" />
            </div>

            {{-- Password --}}
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label for="password" class="block text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Password
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" wire:navigate
                           class="text-xs font-semibold hover:underline transition"
                           style="color: #3A9E3F;">
                            Lupa password?
                        </a>
                    @endif
                </div>
                <div class="relative" x-data="{ show: false }">
                    <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none">
                        <svg class="w-4.5 h-4.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input wire:model="form.password"
                           id="password" :type="show ? 'text' : 'password'" name="password"
                           required autocomplete="current-password"
                           placeholder="••••••••"
                           class="w-full pl-10 pr-11 py-3 rounded-xl border-2 border-gray-200 text-sm font-medium text-gray-800 placeholder-gray-400 focus:border-green-500 focus:ring-0 transition-colors outline-none">
                    <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-3.5 flex items-center text-gray-400 hover:text-gray-600 transition">
                        <svg x-show="!show" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('form.password')" class="mt-1.5" />
            </div>

            {{-- Remember Me --}}
            <div class="flex items-center gap-2.5">
                <input wire:model="form.remember" id="remember" type="checkbox" name="remember"
                       class="w-4 h-4 rounded border-gray-300 focus:ring-green-500 cursor-pointer"
                       style="color: #3A9E3F;">
                <label for="remember" class="text-sm font-medium text-gray-600 cursor-pointer select-none">
                    Ingat saya di perangkat ini
                </label>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    wire:loading.attr="disabled"
                    class="w-full py-3.5 rounded-2xl text-white font-extrabold text-sm transition-all flex items-center justify-center gap-2 mt-2"
                    style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E); box-shadow: 0 6px 20px rgba(58,158,63,0.4);">
                <span wire:loading.remove wire:target="login" class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Masuk ke Akun
                </span>
                <span wire:loading wire:target="login" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Memproses...
                </span>
            </button>

        </form>

        {{-- Divider --}}
        <div class="flex items-center gap-3 my-6">
            <div class="flex-1 h-px bg-gray-200"></div>
            <span class="text-xs text-gray-400 font-medium">Belum punya akun?</span>
            <div class="flex-1 h-px bg-gray-200"></div>
        </div>

        {{-- Register Link --}}
        <a href="{{ route('register') }}" wire:navigate
           class="flex items-center justify-center gap-2 w-full py-3.5 rounded-2xl border-2 border-green-500 text-green-700 font-bold text-sm hover:bg-green-50 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Buat Akun Baru
        </a>

    </div>
</div>