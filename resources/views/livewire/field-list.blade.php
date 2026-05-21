<div class="min-h-screen bg-gray-100">

    {{-- ===== HERO HEADER ===== --}}
    <div class=" overflow-hidden" style="background: linear-gradient(135deg, #1E6B2A 0%, #3A9E3F 60%, #6DBE4E 100%);">
        {{-- Decorative circles --}}
        <div class="absolute -top-10 -right-10 w-64 h-64 rounded-full opacity-10 bg-white pointer-events-none"></div>
        <div class="absolute bottom-0 -left-8 w-44 h-44 rounded-full opacity-10 bg-white pointer-events-none"></div>

        <div class="relative max-w-5xl mx-auto px-4 pt-10 pb-16 sm:pt-12">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('home') }}" wire:navigate
                   class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <span class="text-green-200 text-sm font-semibold">Beranda</span>
                <span class="text-green-300 text-sm">/</span>
                <span class="text-white text-sm font-bold">Semua Lapangan</span>
            </div>

            <h1 class="text-white text-2xl sm:text-3xl font-extrabold leading-tight mt-4 mb-1">
                Temukan Lapangan 🏟️
            </h1>
            <p class="text-green-200 text-sm font-medium mb-6">
                {{ $fields->total() }} lapangan tersedia untuk kamu
            </p>

            {{-- Search Bar --}}
            <div class="relative max-w-xl">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input wire:model.live.debounce.400ms="search"
                       type="text"
                       placeholder="Cari nama atau lokasi lapangan..."
                       class="w-full pl-12 pr-12 py-3.5 rounded-2xl text-sm font-medium text-gray-700 placeholder-gray-400 bg-white border-0 shadow-lg focus:outline-none focus:ring-2 focus:ring-green-300 transition">
                @if($search)
                    <button wire:click="$set('search', '')"
                            class="absolute inset-y-0 right-4 flex items-center text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @else
                    <div wire:loading wire:target="search"
                         class="absolute inset-y-0 right-4 flex items-center">
                        <svg class="w-4 h-4 text-green-500 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 -mt-5">

        {{-- ===== FILTER & SORT BAR ===== --}}
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 mb-6">

            {{-- Type Filter Chips --}}
            @if($types->isNotEmpty())
            <div class="mb-3">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Tipe Lapangan</p>
                <div class="flex flex-wrap gap-2">
                    <button wire:click="setType('')"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold border-2 transition-all
                            {{ $type === '' ? 'border-green-600 bg-green-600 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-green-300' }}">
                        Semua
                    </button>
                    @foreach($types as $t)
                    <button wire:click="setType('{{ $t }}')"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold border-2 capitalize transition-all
                            {{ $type === $t ? 'border-green-600 bg-green-600 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-green-300' }}">
                        {{ $t }}
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Sort --}}
            <div class="flex items-center justify-between flex-wrap gap-3 pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-500 font-medium">
                    Menampilkan
                    <span class="font-bold text-gray-900">{{ $fields->firstItem() ?? 0 }}–{{ $fields->lastItem() ?? 0 }}</span>
                    dari
                    <span class="font-bold text-gray-900">{{ $fields->total() }}</span>
                    lapangan
                </p>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 font-medium">Urutkan:</span>
                    <select wire:model.live="sortBy"
                            class="text-xs font-bold text-gray-700 border-2 border-gray-200 rounded-xl px-3 py-2 focus:border-green-500 focus:ring-0 outline-none bg-white cursor-pointer transition-colors">
                        <option value="latest">Terbaru</option>
                        <option value="name">Nama A–Z</option>
                        <option value="price_asc">Harga Terendah</option>
                        <option value="price_desc">Harga Tertinggi</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- ===== LOADING STATE ===== --}}
        <div wire:loading wire:target="search, sortBy, type, setType"
             class="flex items-center justify-center py-8">
            <div class="flex items-center gap-3 bg-white px-6 py-3 rounded-2xl shadow-md border border-gray-100">
                <svg class="w-5 h-5 animate-spin text-green-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-600">Mencari lapangan...</span>
            </div>
        </div>

        {{-- ===== FIELD GRID ===== --}}
        <div wire:loading.remove wire:target="search, sortBy, type, setType">

            @if($fields->isEmpty())
                {{-- Empty State --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center mb-6">
                    <div class="text-6xl mb-4">🏟️</div>
                    <h3 class="font-extrabold text-gray-700 text-lg mb-2">Lapangan Tidak Ditemukan</h3>
                    <p class="text-gray-400 text-sm mb-6">
                        @if($search)
                            Tidak ada lapangan yang cocok dengan "<span class="font-bold text-gray-600">{{ $search }}</span>"
                        @else
                            Belum ada lapangan yang tersedia saat ini.
                        @endif
                    </p>
                    @if($search || $type)
                    <button wire:click="$set('search', ''); $set('type', '')"
                            class="inline-flex items-center gap-2 py-2.5 px-5 rounded-xl text-white text-sm font-bold transition"
                            style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E);">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset Filter
                    </button>
                    @endif
                </div>

            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">
                    @foreach($fields as $field)
                    <a href="{{ route('field.detail', $field->id) }}" wire:navigate
                       class="group block bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-md hover:shadow-xl hover:border-green-200 hover:-translate-y-1 transition-all duration-300">

                        {{-- Image --}}
                        <div class="relative overflow-hidden">
                            @if($field->getFirstMediaUrl('gallery'))
                                <img src="{{ $field->getFirstMediaUrl('gallery') }}"
                                     class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-500"
                                     alt="{{ $field->name }}">
                            @else
                                <div class="w-full h-48 flex items-center justify-center text-5xl"
                                     style="background: linear-gradient(135deg, #DCFCE7, #BBF7D0);">
                                    ⚽
                                </div>
                            @endif

                            {{-- Badges --}}
                            <div class="absolute top-3 left-3">
                                @if($field->type)
                                <span class="bg-white/90 backdrop-blur-sm text-gray-700 text-[10px] font-bold px-2.5 py-1 rounded-full capitalize">
                                    {{ $field->type }}
                                </span>
                                @endif
                            </div>
                            <div class="absolute top-3 right-3">
                                <span class="bg-yellow-400 text-yellow-900 text-[10px] font-extrabold px-2 py-1 rounded-full">
                                    ★ 4.8
                                </span>
                            </div>

                            {{-- Hover overlay --}}
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                 style="background: rgba(30, 107, 42, 0.55);">
                                <span class="text-white font-extrabold text-sm flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Lihat Detail
                                </span>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="p-4">
                            <h3 class="font-extrabold text-gray-900 text-base mb-1 line-clamp-1">
                                {{ $field->name }}
                            </h3>
                            <div class="flex items-center gap-1 text-gray-400 text-xs mb-3">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="line-clamp-1">{{ $field->address }}</span>
                            </div>

                            {{-- Facilities --}}
                            @if($field->facilities && count($field->facilities) > 0)
                            <div class="flex flex-wrap gap-1 mb-3">
                                @foreach(array_slice($field->facilities, 0, 2) as $fas)
                                <span class="bg-gray-100 text-gray-500 text-[10px] font-semibold px-2 py-0.5 rounded-md">
                                    {{ $fas }}
                                </span>
                                @endforeach
                                @if(count($field->facilities) > 2)
                                <span class="bg-gray-100 text-gray-400 text-[10px] font-semibold px-2 py-0.5 rounded-md">
                                    +{{ count($field->facilities) - 2 }}
                                </span>
                                @endif
                            </div>
                            @endif

                            {{-- Price & CTA --}}
                            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-medium">Mulai dari</p>
                                    <p class="font-extrabold text-base" style="color: #3A9E3F;">
                                        Rp {{ number_format($field->prices->min('price') ?? 0, 0, ',', '.') }}
                                        <span class="text-xs font-normal text-gray-400">/jam</span>
                                    </p>
                                </div>
                                <span class="flex items-center gap-1 text-[10px] font-bold text-green-700 bg-green-50 border border-green-100 px-3 py-1.5 rounded-xl group-hover:bg-green-600 group-hover:text-white group-hover:border-green-600 transition-all">
                                    Booking
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>

                {{-- ===== PAGINATION ===== --}}
                @if($fields->hasPages())
                <div class="pb-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                        {{-- Custom pagination --}}
                        <div class="flex items-center justify-between flex-wrap gap-3">
                            <p class="text-xs text-gray-500 font-medium">
                                Halaman <span class="font-bold text-gray-800">{{ $fields->currentPage() }}</span>
                                dari <span class="font-bold text-gray-800">{{ $fields->lastPage() }}</span>
                            </p>
                            <div class="flex items-center gap-1">
                                {{-- Previous --}}
                                @if($fields->onFirstPage())
                                    <span class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center opacity-40 cursor-not-allowed">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                    </span>
                                @else
                                    <button wire:click="previousPage"
                                            class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-green-100 hover:text-green-700 transition-all">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                @endif

                                {{-- Page Numbers --}}
                                @foreach($fields->getUrlRange(max(1, $fields->currentPage() - 2), min($fields->lastPage(), $fields->currentPage() + 2)) as $page => $url)
                                    @if($page == $fields->currentPage())
                                        <span class="w-9 h-9 rounded-xl text-white text-sm font-extrabold flex items-center justify-center"
                                              style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E);">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <button wire:click="gotoPage({{ $page }})"
                                                class="w-9 h-9 rounded-xl bg-gray-100 text-gray-600 text-sm font-semibold flex items-center justify-center hover:bg-green-50 hover:text-green-700 transition-all">
                                            {{ $page }}
                                        </button>
                                    @endif
                                @endforeach

                                {{-- Next --}}
                                @if($fields->hasMorePages())
                                    <button wire:click="nextPage"
                                            class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-green-100 hover:text-green-700 transition-all">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                @else
                                    <span class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center opacity-40 cursor-not-allowed">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            @endif
        </div>

    </div>
</div>