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

{{-- PERBAIKAN Z-INDEX: Menambahkan relative dan z-50 agar dropdown menimpa seluruh konten di bawahnya --}}
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm relative z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            {{-- Logo --}}
            <div class="flex items-center gap-10">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #1E6B2A, #6DBE4E);">
                        <img src="{{asset('images/logo.png')}}" alt="Logo" class="w-6 h-6 object-contain">
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

            {{-- Right Side (Desktop & Mobile) --}}
            <div class="flex items-center gap-2 sm:gap-3">
                
                @auth
                {{-- ===== NOTIFICATION SECTION (Tampil di Desktop & Mobile) ===== --}}
                <div class="relative" x-data="{ openNotif: false, isCleared: false }">

                    @php
                    // 1. Ambil ID User & Tanggal
                    $userId = auth()->id();
                    $today = \Carbon\Carbon::today()->format('Y-m-d');

                    // 2. Kueri Jadwal Main Hari Ini (Lunas/DP)
                    $gameToday = \App\Models\Booking::where('user_id', $userId)
                        ->whereDate('booking_date', $today)
                        ->whereIn('status', ['paid', 'dp_paid'])
                        ->with('field')
                        ->first();

                    // 3. Kueri 5 aktivitas terakhir
                    $recentBookings = \App\Models\Booking::where('user_id', $userId)
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();

                    // Cek Notifikasi
                    $hasNotification = $gameToday || $recentBookings->count() > 0;
                    @endphp

                    {{-- Bell Button (Warna diubah ke abu-abu untuk Navbar Putih) --}}
                    <button @click="openNotif = !openNotif" @click.outside="openNotif = false"
                        class="relative w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gray-50 flex items-center justify-center hover:bg-gray-100 border border-gray-100 transition shadow-sm focus:outline-none">
                        
                        <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>

                        {{-- Titik Merah Notifikasi --}}
                        @if($hasNotification)
                        <span x-show="!isCleared" class="absolute top-1 right-1 sm:top-2 sm:right-2 flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500 border border-white"></span>
                        </span>
                        @endif
                    </button>

                    {{-- Dropdown Notification Panel --}}
                    <div x-show="openNotif"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                        class="absolute top-[calc(100%+12px)] right-0 w-[85vw] max-w-[360px] sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-visible origin-top-right"
                        style="display: none;">

                        {{-- Segitiga Pointer --}}
                        <div class="absolute -top-2 right-4 sm:right-5 w-4 h-4 bg-gray-50 border-l border-t border-gray-100 transform rotate-45 z-0"></div>

                        <div class="relative bg-gray-50 border-b border-gray-100 px-4 py-3 flex justify-between items-center rounded-t-2xl z-10">
                            <h3 class="font-extrabold text-gray-800 text-sm">Notifikasi</h3>
                            <div class="flex items-center gap-3">
                                @if($hasNotification)
                                <button x-show="!isCleared" @click="isCleared = true" class="text-[10px] font-bold text-gray-500 hover:text-red-500 transition focus:outline-none">Bersihkan</button>
                                @endif
                                <a href="{{ route('booking.history') }}" wire:navigate class="text-[10px] font-bold text-green-600 hover:text-green-700">Lihat Riwayat</a>
                            </div>
                        </div>

                        <div class="max-h-[350px] overflow-y-auto relative z-10 bg-white rounded-b-2xl">
                            
                            {{-- Daftar Notifikasi --}}
                            <div x-show="!isCleared && {{ $hasNotification ? 'true' : 'false' }}">
                                
                                {{-- Pengingat Hari Ini --}}
                                @if($gameToday)
                                <a href="{{ route('booking.history') }}" wire:navigate class="block p-4 border-b border-gray-50 bg-green-50/50 hover:bg-green-50 transition cursor-pointer">
                                    <div class="flex gap-3">
                                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                            <span class="text-lg">⚽</span>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-green-800 mb-0.5">Jadwal Main Hari Ini!</p>
                                            <p class="text-[11px] text-gray-600 font-medium leading-tight">
                                                Jangan lupa, kamu ada jadwal di <span class="font-bold text-gray-800">{{ $gameToday->field->name ?? 'Lapangan' }}</span>.
                                            </p>
                                            <p class="text-[9px] text-green-600 font-bold mt-1 uppercase tracking-wider">
                                                KODE: {{ $gameToday->booking_code }}
                                            </p>
                                        </div>
                                    </div>
                                </a>
                                @endif

                                {{-- Riwayat Pemesanan --}}
                                @foreach($recentBookings as $booking)
                                @php
                                if(in_array($booking->status, ['paid', 'dp_paid'])) {
                                    $icon = '✓';
                                    $colorClass = 'bg-blue-100 text-blue-600';
                                    $title = 'Pembayaran Berhasil';
                                    $desc = 'Booking untuk ' . \Carbon\Carbon::parse($booking->booking_date)->format('d M') . ' dikonfirmasi.';
                                } elseif($booking->status == 'cancelled') {
                                    $icon = '✕';
                                    $colorClass = 'bg-red-100 text-red-600';
                                    $title = 'Booking Dibatalkan';
                                    $desc = 'Pesanan pada ' . \Carbon\Carbon::parse($booking->booking_date)->format('d M') . ' dibatalkan.';
                                } else {
                                    $icon = '⏳';
                                    $colorClass = 'bg-yellow-100 text-yellow-600';
                                    $title = 'Menunggu Pembayaran';
                                    $desc = 'Selesaikan pembayaran pesanan ' . $booking->booking_code . '.';
                                }
                                @endphp
                                <a href="{{ route('booking.history') }}" wire:navigate class="block p-4 border-b border-gray-50 bg-white hover:bg-gray-50 transition">
                                    <div class="flex gap-3">
                                        <div class="w-9 h-9 rounded-full {{ $colorClass }} flex items-center justify-center font-black flex-shrink-0 text-sm">
                                            {{ $icon }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-800 mb-0.5">{{ $title }}</p>
                                            <p class="text-[10px] text-gray-500 font-medium leading-tight line-clamp-2">{{ $desc }}</p>
                                            <p class="text-[9px] text-gray-400 font-semibold mt-1">{{ $booking->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>

                            {{-- Tampilan Kosong / Dibersihkan --}}
                            <div x-show="isCleared || {{ $hasNotification ? 'false' : 'true' }}" class="p-8 text-center" {{ $hasNotification ? 'style="display: none;"' : '' }}>
                                <div class="text-4xl mb-2 text-gray-300">📭</div>
                                <p class="text-xs font-bold text-gray-500">Belum ada notifikasi</p>
                                <p class="text-[10px] text-gray-400 mt-1">Aktivitas booking kamu akan muncul di sini.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== PROFILE DROPDOWN (Hanya Desktop) ===== --}}
                <div class="hidden sm:block">
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
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Profil Saya
                                </div>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('booking.history')" wire:navigate>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Riwayat Booking
                                </div>
                            </x-dropdown-link>
                            <div class="border-t border-gray-100 mt-1">
                                <button wire:click="logout" class="w-full text-start">
                                    <x-dropdown-link>
                                        <div class="flex items-center gap-2 text-red-500">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Keluar
                                        </div>
                                    </x-dropdown-link>
                                </button>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>
                
                @else
                {{-- Login / Register (Desktop) --}}
                <div class="hidden sm:flex items-center gap-3">
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 px-4 py-2 rounded-lg hover:bg-gray-50 transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="text-sm font-bold text-white px-5 py-2 rounded-xl transition" style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E); box-shadow: 0 4px 14px rgba(58,158,63,0.3);">
                        Daftar
                    </a>
                </div>
                @endauth

                {{-- Mobile Hamburger (Tampil di Mobile, di sebelah kanan lonceng) --}}
                <div class="flex items-center sm:hidden">
                    <button @click="open = !open" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- Mobile Dropdown Menu --}}
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden border-t border-gray-100 absolute w-full bg-white z-40 shadow-lg">
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
        <div class="px-4 py-4 border-t border-gray-100 flex gap-3 bg-gray-50">
            <a href="{{ route('login') }}" wire:navigate class="flex-1 text-center py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 bg-white hover:border-green-300 transition">Masuk</a>
            <a href="{{ route('register') }}" wire:navigate class="flex-1 text-center py-2.5 rounded-xl text-sm font-bold text-white transition" style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E);">Daftar</a>
        </div>
        @endauth
    </div>
</nav>