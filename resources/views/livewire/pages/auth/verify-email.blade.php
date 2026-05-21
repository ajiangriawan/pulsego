<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('home', absolute: false), navigate: true);
            return;
        }
        Auth::user()->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }

    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <div class="px-8 pt-8 pb-6" style="background: linear-gradient(135deg, #1E6B2A, #3A9E3F);">
        <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center mb-3">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"/>
            </svg>
        </div>
        <h1 class="text-2xl font-extrabold text-white mb-1">Verifikasi Email ✉️</h1>
        <p class="text-green-200 text-sm font-medium">Satu langkah lagi untuk mulai bermain!</p>
    </div>

    <div class="px-8 py-7">

        <div class="bg-green-50 border border-green-100 rounded-2xl p-4 mb-6 flex gap-3">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-green-700 font-medium leading-relaxed">
                Terima kasih telah mendaftar! Sebelum memulai, mohon verifikasi alamat email kamu dengan mengklik tautan yang kami kirimkan.
            </p>
        </div>

        @if(session('status') == 'verification-link-sent')
            <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-5 flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-bold text-green-700">Link verifikasi baru telah dikirim ke email kamu!</p>
            </div>
        @endif

        <div class="space-y-3">
            <button wire:click="sendVerification"
                    wire:loading.attr="disabled"
                    class="w-full py-3.5 rounded-2xl text-white font-extrabold text-sm transition-all flex items-center justify-center gap-2"
                    style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E); box-shadow: 0 6px 20px rgba(58,158,63,0.4);">
                <span wire:loading.remove wire:target="sendVerification" class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Kirim Ulang Email Verifikasi
                </span>
                <span wire:loading wire:target="sendVerification" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Mengirim...
                </span>
            </button>

            <button wire:click="logout"
                    class="w-full py-3 rounded-2xl border-2 border-gray-200 text-gray-500 font-semibold text-sm hover:border-red-300 hover:text-red-500 hover:bg-red-50 transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Keluar dari Akun
            </button>
        </div>

    </div>
</div>