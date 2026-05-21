<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            {{-- Logo --}}
            <div class="flex items-center gap-10">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #1E6B2A, #6DBE4E);">
                        <img src="{{asset('images/logo.png')}}" alt="">
                    </div>
                    <span class="text-xl font-extrabold tracking-tight" style="color: #1E6B2A;">PulseGo</span>
                </a>

                {{-- Desktop Nav Links --}}
                <div class="hidden sm:flex items-center gap-1">
                    <a href="{{ route('home') }}" wire:navigate
                       class="px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('home') ? 'bg-green-50 text-green-700' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' }}">
                        Beranda
                    </a>
                    <a href="{{ route('booking.history') }}" wire:navigate
                       class="px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('booking.history') ? 'bg-green-50 text-green-700' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' }}">
                        Riwayat Booking
                    </a>
                </div>
            </div>

            {{-- Right Side --}}
            <div class="hidden sm:flex items-center gap-3">
                @auth
                    <x-dropdown align="right" width="52">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl border border-gray-200 hover:border-green-300 hover:bg-green-50 transition-all">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E);">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-semibold text-gray-700"
                                      x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                                      x-text="name"
                                      x-on:profile-updated.window="name = $event.detail.name">
                                </span>
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-xs text-gray-400 font-medium">Masuk sebagai</p>
                                <p class="text-sm font-bold text-gray-800 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <x-dropdown-link :href="route('profile')" wire:navigate>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Profil Saya
                                </div>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('booking.history')" wire:navigate>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Riwayat Booking
                                </div>
                            </x-dropdown-link>
                            <div class="border-t border-gray-100 mt-1">
                                <button wire:click="logout" class="w-full text-start">
                                    <x-dropdown-link>
                                        <div class="flex items-center gap-2 text-red-500">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Keluar
                                        </div>
                                    </x-dropdown-link>
                                </button>
                            </div>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 px-4 py-2 rounded-lg hover:bg-gray-50 transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="text-sm font-bold text-white px-5 py-2 rounded-xl transition" style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E); box-shadow: 0 4px 14px rgba(58,158,63,0.3);">
                        Daftar
                    </a>
                @endauth
            </div>

            {{-- Mobile Hamburger --}}
            <div class="flex items-center sm:hidden">
                <button @click="open = !open" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden border-t border-gray-100">
        <div class="px-4 pt-2 pb-4 space-y-1">
            <a href="{{ route('home') }}" wire:navigate class="block px-4 py-2.5 rounded-lg text-sm font-semibold {{ request()->routeIs('home') ? 'bg-green-50 text-green-700' : 'text-gray-600 hover:bg-gray-50' }}">
                Beranda
            </a>
            <a href="{{ route('booking.history') }}" wire:navigate class="block px-4 py-2.5 rounded-lg text-sm font-semibold {{ request()->routeIs('booking.history') ? 'bg-green-50 text-green-700' : 'text-gray-600 hover:bg-gray-50' }}">
                Riwayat Booking
            </a>
        </div>

        @auth
        <div class="px-4 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold text-white" style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E);">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-400">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <a href="{{ route('profile') }}" wire:navigate class="block px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 hover:bg-white hover:text-green-700 transition">Profil Saya</a>
            <button wire:click="logout" class="w-full text-left px-4 py-2 rounded-lg text-sm font-semibold text-red-500 hover:bg-red-50 transition">Keluar</button>
        </div>
        @else
        <div class="px-4 py-4 border-t border-gray-100 flex gap-3">
            <a href="{{ route('login') }}" wire:navigate class="flex-1 text-center py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:border-green-300 transition">Masuk</a>
            <a href="{{ route('register') }}" wire:navigate class="flex-1 text-center py-2.5 rounded-xl text-sm font-bold text-white transition" style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E);">Daftar</a>
        </div>
        @endauth
    </div>
</nav>