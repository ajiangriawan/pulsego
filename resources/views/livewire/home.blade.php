<div class="min-h-screen bg-gray-50 pb-24 md:pb-12">

    {{-- ===== HERO / HEADER SECTION WITH GRADIENT ===== --}}
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1E6B2A 0%, #3A9E3F 60%, #6DBE4E 100%);">
        {{-- Decorative background elements --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-10 -right-10 w-56 h-56 rounded-full opacity-10" style="background: rgba(255,255,255,0.3);"></div>
            <div class="absolute bottom-0 -left-8 w-40 h-40 rounded-full opacity-10" style="background: rgba(255,255,255,0.2);"></div>
            <div class="absolute top-1/2 right-1/4 w-20 h-20 rounded-full opacity-5" style="background: white;"></div>
        </div>

        <div class="relative max-w-2xl mx-auto px-4 pt-8 pb-14 sm:pt-12 sm:pb-16">
            {{-- Personal Greeting Header --}}
            <div class="flex justify-between items-start mb-6">
                <div>
                    <p class="text-green-200 text-xs sm:text-sm font-medium mb-1">
                        👋 Halo, {{ auth()->user()->name ?? 'Sobat Olahraga' }}!
                    </p>
                    <h1 class="text-white text-2xl sm:text-3xl font-extrabold leading-tight tracking-tight">
                        Saatnya bermain<br>hari ini! ⚽
                    </h1>
                    
                    {{-- Status Pemantauan Posisi GPS Customer --}}
                    <div class="text-[10px] mt-2 flex items-center gap-1 font-semibold">
                        @if(isset($userLat) && isset($userLng))
                            <span class="text-white bg-white/20 backdrop-blur-md px-2 py-0.5 rounded-md flex items-center gap-1">📍 Jarak disesuaikan dengan lokasi kamu</span>
                        @elseif(isset($locationError) && $locationError)
                            <span class="text-amber-200">⚠️ {{ $locationError }} (Urutan standar)</span>
                        @else
                            <span class="text-green-200 animate-pulse flex items-center gap-1">⏳ Mencari koordinat GPS terdekat...</span>
                        @endif
                    </div>
                </div>
                
                <div class="flex items-center gap-3 mt-1">
                    
                    <div class="w-10 h-10 rounded-full bg-white/30 flex items-center justify-center text-white font-black text-base border-2 border-white/50 shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name ?? 'G', 0, 1)) }}
                    </div>
                </div>
            </div>

            {{-- Livewire-backed Search Field Bar --}}
            <div class="relative">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.400ms="search"
                       placeholder="Cari lapangan atau lokasi olahraga terdekat..."
                       class="w-full pl-12 pr-4 py-3.5 rounded-2xl text-sm font-medium text-gray-700 placeholder-gray-400 bg-white border-0 shadow-xl focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200">
            </div>
        </div>
    </div>

    {{-- ===== MAIN WRAPPER BODY CONTAINER ===== --}}
    <div class="max-w-2xl mx-auto px-4 -mt-6 relative z-10">

        {{-- ===== QUICK INTEGRATED MENU NAVIGATION CARDS ===== --}}
        <div class="grid grid-cols-3 gap-3 mb-8">
            <a href="{{ route('field') }}" wire:navigate
                class="flex flex-col items-center gap-2 p-3 bg-white rounded-2xl shadow-md border border-gray-100 hover:border-green-200 hover:shadow-lg transition-all group">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200" style="background: linear-gradient(135deg, #DCFCE7, #BBF7D0);">
                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-gray-700 text-center leading-tight">Eksplor</span>
            </a>

            <a href="{{ route('booking.history') }}" wire:navigate
                class="flex flex-col items-center gap-2 p-3 bg-white rounded-2xl shadow-md border border-gray-100 hover:border-green-200 hover:shadow-lg transition-all group">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200" style="background: linear-gradient(135deg, #FEF3C7, #FDE68A);">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-gray-700 text-center leading-tight">Riwayat</span>
            </a>

            <!-- <a href="{{ route('booking.history.payment') }}"
                class="flex flex-col items-center gap-2 p-3 bg-white rounded-2xl shadow-md border border-gray-100 hover:border-green-200 hover:shadow-lg transition-all group">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200" style="background: linear-gradient(135deg, #EDE9FE, #DDD6FE);">
                    <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-gray-700 text-center leading-tight">Bayar Sisa</span>
            </a> -->

            <a href="{{ route('profile') }}" wire:navigate
                class="flex flex-col items-center gap-2 p-3 bg-white rounded-2xl shadow-md border border-gray-100 hover:border-green-200 hover:shadow-lg transition-all group">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200" style="background: linear-gradient(135deg, #DBEAFE, #BFDBFE);">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-gray-700 text-center leading-tight">Profil</span>
            </a>
        </div>

        {{-- ===== PROMO INTERACTIVE MARKETING BANNER ===== --}}
        <div class="relative overflow-hidden rounded-2xl mb-8 p-5 shadow-lg" style="background: linear-gradient(135deg, #1E6B2A 0%, #3A9E3F 100%);">
            <div class="absolute -right-4 -bottom-4 w-28 h-28 rounded-full opacity-10 bg-white pointer-events-none"></div>
            <div class="absolute right-8 -top-6 w-16 h-16 rounded-full opacity-10 bg-white pointer-events-none"></div>
            <div class="relative flex justify-between items-center">
                <div>
                    <div class="inline-block bg-yellow-400 text-yellow-900 text-[10px] font-black px-2.5 py-0.5 rounded-full mb-2 uppercase tracking-wider">
                        Pengguna Baru
                    </div>
                    <h3 class="text-white font-extrabold text-xl leading-tight mb-1">Diskon 50% 🎉</h3>
                    <p class="text-green-100 text-xs font-medium opacity-90">Untuk booking pertama kamu di Palembang!</p>
                    <button class="mt-3 bg-white text-green-700 text-xs font-bold px-4 py-2 rounded-xl hover:bg-green-50 active:scale-95 transition shadow-sm">
                        Gunakan Kode
                    </button>
                </div>
                <div class="text-5xl opacity-90 select-none animate-bounce">⚽</div>
            </div>
        </div>

        {{-- ===== SECTION ROW TITLE ===== --}}
        <div class="flex justify-between items-center mb-4 px-1">
            <h2 class="text-base font-extrabold text-gray-900 tracking-tight">
                {{ isset($userLat) && $userLat ? 'Rekomendasi Lapangan Terdekat 📍' : 'Lapangan Populer 🔥' }}
            </h2>
            <a href="{{ route('field') }}" class="text-xs font-bold flex items-center gap-1 transition-colors hover:text-green-700" style="color: #3A9E3F;">
                Lihat Semua
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        {{-- ===== HARDENED CARDS GRID SYSTEM ===== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-6">
            @forelse($fields->take(2) as $field)
            <a href="{{ route('field.detail', $field->id) }}" wire:navigate wire:key="home-popular-field-{{ $field->id }}"
                class="block bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-md hover:shadow-xl hover:border-green-200 transition-all duration-300 group flex flex-col h-full">

                {{-- Image Cover Container Box --}}
                <div class="relative overflow-hidden aspect-video bg-gray-50 shrink-0">
                    @if($field->getFirstMediaUrl('gallery'))
                    <img src="{{ $field->getFirstMediaUrl('gallery') }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        alt="{{ $field->name }}">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-4xl" style="background: linear-gradient(135deg, #DCFCE7, #BBF7D0);">⚽</div>
                    @endif

                    {{-- Badges Absolute Area --}}
                    <div class="absolute top-3 left-3 z-10">
                        <span class="bg-white/95 text-gray-700 text-[10px] font-extrabold px-3 py-1 rounded-full backdrop-blur-sm border shadow-xs capitalize tracking-wide">
                            📌 {{ $field->type ?? 'Olahraga' }}
                        </span>
                    </div>
                    <div class="absolute top-3 right-3 z-10">
                        <span class="bg-amber-400 text-amber-950 text-[10px] font-black px-2.5 py-1 rounded-full shadow-xs flex items-center gap-0.5">
                            ★ 4.8
                        </span>
                    </div>

                    {{-- DYNAMIC GPS INTERACTION DATA: Tampilkan Jarak secara Real-time --}}
                    @if(isset($field->distance))
                    <div class="absolute bottom-3 left-3 z-10">
                        <span class="bg-emerald-600/90 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg backdrop-blur-xs shadow-md border border-emerald-500/20">
                            📍 {{ number_format($field->distance, 1) }} km dari lokasi kamu
                        </span>
                    </div>
                    @endif
                </div>

                {{-- Text Content Information Area --}}
                <div class="p-4 flex flex-col justify-between flex-1">
                    <div>
                        <h3 class="font-extrabold text-gray-900 text-base mb-1 line-clamp-1 group-hover:text-green-700 transition-colors">
                            {{ $field->name }}
                        </h3>
                        <div class="flex items-start gap-1 text-gray-400 text-xs mb-4">
                            <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="line-clamp-1 text-gray-500">{{ $field->address }}</span>
                        </div>
                    </div>

                    {{-- Price and CTA Button Flex Row Footer --}}
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
                <p class="font-bold text-gray-700 text-sm">Belum Ada Lapangan</p>
                <p class="text-xs text-gray-400 mt-0.5">Daftar rekomendasi area olahraga akan muncul di sini.</p>
            </div>
            @endforelse
        </div>

    </div>
</div>

{{-- ===== HIGH-ACCURACY JAVASCRIPT GEOLOCATION DETECTOR ===== --}}
@script
<script>
    document.addEventListener('livewire:navigated', () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    // Kirim data koordinat ke listener component Livewire
                    Livewire.dispatch('setUserLocation', {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    });
                },
                (error) => {
                    let errorMsg = 'Izin lokasi GPS dinonaktifkan';
                    if (error.code === 2) errorMsg = 'Sinyal perangkat tidak akurat';
                    Livewire.dispatch('setLocationError', { errorMessage: errorMsg });
                },
                { enableHighAccuracy: true, timeout: 7000 }
            );
        } else {
            Livewire.dispatch('setLocationError', { errorMessage: 'Perangkat tidak mendukung Geolocation' });
        }
    });
</script>
@endscript