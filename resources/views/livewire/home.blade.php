<div class="min-h-screen bg-gray-100">

    {{-- ===== HERO / HEADER SECTION ===== --}}
    <div class=" overflow-hidden" style="background: linear-gradient(135deg, #1E6B2A 0%, #3A9E3F 60%, #6DBE4E 100%);">
        {{-- Decorative blobs --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-10 -right-10 w-56 h-56 rounded-full opacity-10" style="background: rgba(255,255,255,0.3);"></div>
            <div class="absolute bottom-0 -left-8 w-40 h-40 rounded-full opacity-10" style="background: rgba(255,255,255,0.2);"></div>
            <div class="absolute top-1/2 right-1/4 w-20 h-20 rounded-full opacity-5" style="background: white;"></div>
        </div>

        <div class="relative max-w-2xl mx-auto px-4 pt-8 pb-14 sm:pt-12 sm:pb-16">
            {{-- Greeting --}}
            <div class="flex justify-between items-start mb-6">
                <div>
                    <p class="text-green-200 text-sm font-medium mb-1">
                        👋 Halo, {{ auth()->user()->name ?? 'Pemain' }}!
                    </p>
                    <h1 class="text-white text-2xl sm:text-3xl font-extrabold leading-tight">
                        Saatnya bermain<br>hari ini! ⚽
                    </h1>
                </div>
                <div class="flex items-center gap-3 mt-1">
                    <button class="relative w-10 h-10 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>
                    <div class="w-10 h-10 rounded-full bg-white/30 flex items-center justify-center text-white font-extrabold text-base border-2 border-white/50">
                        {{ strtoupper(substr(auth()->user()->name ?? 'G', 0, 1)) }}
                    </div>
                </div>
            </div>

            {{-- Search Bar --}}
            <div class="relative">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text"
                    placeholder="Cari lapangan olahraga..."
                    class="w-full pl-12 pr-4 py-3.5 rounded-2xl text-sm font-medium text-gray-700 placeholder-gray-400 bg-white border-0 shadow-lg focus:outline-none focus:ring-2 focus:ring-green-300">
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 -mt-6">

        {{-- ===== QUICK MENU CARDS ===== --}}
        <div class="grid grid-cols-4 gap-3 mb-8">
            <a href="{{ route('field') }}" wire:navigate
                class="flex flex-col items-center gap-2 p-3 bg-white rounded-2xl shadow-md border border-gray-100 hover:border-green-200 hover:shadow-lg transition-all group">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform" style="background: linear-gradient(135deg, #DCFCE7, #BBF7D0);">
                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-gray-700 text-center leading-tight">Booking</span>
            </a>

            <a href="{{ route('booking.history') }}" wire:navigate
                class="flex flex-col items-center gap-2 p-3 bg-white rounded-2xl shadow-md border border-gray-100 hover:border-green-200 hover:shadow-lg transition-all group">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform" style="background: linear-gradient(135deg, #FEF3C7, #FDE68A);">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-gray-700 text-center leading-tight">Riwayat</span>
            </a>

            <a href="{{ route('booking.history.payment') }}"
                class="flex flex-col items-center gap-2 p-3 bg-white rounded-2xl shadow-md border border-gray-100 hover:border-green-200 hover:shadow-lg transition-all group">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform" style="background: linear-gradient(135deg, #EDE9FE, #DDD6FE);">
                    <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-gray-700 text-center leading-tight">Bayar</span>
            </a>

            <a href="{{ route('profile') }}" wire:navigate
                class="flex flex-col items-center gap-2 p-3 bg-white rounded-2xl shadow-md border border-gray-100 hover:border-green-200 hover:shadow-lg transition-all group">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform" style="background: linear-gradient(135deg, #DBEAFE, #BFDBFE);">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-gray-700 text-center leading-tight">Profil</span>
            </a>
        </div>

        {{-- ===== PROMO BANNER ===== --}}
        <div class="relative overflow-hidden rounded-2xl mb-8 p-5" style="background: linear-gradient(135deg, #1E6B2A 0%, #3A9E3F 100%);">
            <div class="absolute -right-4 -bottom-4 w-28 h-28 rounded-full opacity-10 bg-white"></div>
            <div class="absolute right-8 -top-6 w-16 h-16 rounded-full opacity-10 bg-white"></div>
            <div class="relative flex justify-between items-center">
                <div>
                    <div class="inline-block bg-yellow-400 text-yellow-900 text-xs font-black px-2.5 py-0.5 rounded-full mb-2 uppercase tracking-wide">
                        Pengguna Baru
                    </div>
                    <h3 class="text-white font-extrabold text-xl leading-tight mb-1">Diskon 10% 🎉</h3>
                    <p class="text-green-200 text-xs font-medium">Untuk booking pertama kamu!</p>
                    <button class="mt-3 bg-white text-green-700 text-xs font-bold px-4 py-2 rounded-xl hover:bg-green-50 transition shadow-sm">
                        Gunakan Kode
                    </button>
                </div>
                <div class="text-6xl opacity-90 select-none">⚽</div>
            </div>
        </div>

        {{-- ===== HERO HEADER / LAPANGAN POPULER ===== --}}
        <div class="flex justify-between items-center mb-4 px-1">
            <h2 class="text-lg font-extrabold text-gray-900 tracking-tight">Lapangan Populer 🔥</h2>
            <a href="{{ route('field') }}" class="text-xs font-bold flex items-center gap-1 transition-colors hover:text-green-700" style="color: #3A9E3F;">
                Lihat Semua
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        {{-- Grid Layout: Otomatis memotong data dan menampilkan hanya 2 lapangan teratas --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-4">
            @forelse($fields->take(2) as $field)
            <a href="{{ route('field.detail', $field->id) }}" wire:navigate wire:key="popular-field-{{ $field->id }}"
                class="block bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-md hover:shadow-lg hover:border-green-200 transition-all duration-300 group flex flex-col h-full">

                {{-- Bagian Media / Image Image Cover --}}
                <div class="relative overflow-hidden aspect-video bg-gray-50 shrink-0">
                    @if($field->getFirstMediaUrl('gallery'))
                    <img src="{{ $field->getFirstMediaUrl('gallery') }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        alt="{{ $field->name }}">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-4xl" style="background: linear-gradient(135deg, #DCFCE7, #BBF7D0);">⚽</div>
                    @endif

                    {{-- Badges Deskripsi --}}
                    <div class="absolute top-3 left-3">
                        <span class="bg-white/95 text-gray-700 text-[10px] font-extrabold px-3 py-1 rounded-full backdrop-blur-sm border shadow-xs capitalize">
                            📌 {{ $field->type ?? 'Futsal' }}
                        </span>
                    </div>
                    <div class="absolute top-3 right-3">
                        <span class="bg-amber-400 text-amber-950 text-[10px] font-black px-2.5 py-1 rounded-full shadow-xs flex items-center gap-0.5">
                            ★ 4.8
                        </span>
                    </div>
                </div>

                {{-- Bagian Detail Informasi Konten --}}
                <div class="p-4 flex flex-col justify-between flex-1">
                    <div>
                        <h3 class="font-extrabold text-gray-900 text-base mb-1 line-clamp-1 group-hover:text-green-700 transition-colors">
                            {{ $field->name }}
                        </h3>
                        <div class="flex items-center gap-1 text-gray-400 text-xs mb-4">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="line-clamp-1 text-gray-500">{{ $field->address }}</span>
                        </div>
                    </div>

                    {{-- Footer Harga & CTA Tombol --}}
                    <div class="flex justify-between items-center pt-3 border-t border-gray-50 mt-auto">
                        <div>
                            <span class="font-black text-base" style="color: #3A9E3F;">
                                Rp {{ number_format($field->prices->first()->price ?? 0, 0, ',', '.') }}
                            </span>
                            <span class="text-gray-400 font-normal text-xs">/jam</span>
                        </div>
                        <span class="text-[10px] font-extrabold text-green-700 bg-green-50/80 border border-green-100 px-3 py-1.5 rounded-xl group-hover:bg-green-600 group-hover:text-white group-hover:border-green-600 transition-all duration-300">
                            Booking &rarr;
                        </span>
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-full text-center bg-white border rounded-2xl py-12 shadow-sm">
                <div class="text-5xl mb-2">🏟️</div>
                <p class="font-bold text-gray-700 text-sm">Belum Ada Lapangan Populer</p>
                <p class="text-xs text-gray-400 mt-0.5">Daftar rekomendasi area olahraga akan muncul di sini.</p>
            </div>
            @endforelse
        </div>

    </div>
</div>