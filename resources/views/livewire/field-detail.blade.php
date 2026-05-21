<div class="min-h-screen bg-gray-100 pb-8">

    {{-- Back Button & Hero --}}
    <div class="relative">
        {{-- Gallery --}}
        @php $mediaItems = $field->getMedia('gallery'); @endphp

        @if($mediaItems->count() > 0)
            <div class="relative">
                <img src="{{ $mediaItems->first()->getUrl() }}"
                     class="w-full h-72 sm:h-96 object-cover"
                     alt="{{ $field->name }}">
                {{-- Gradient overlay --}}
                <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 50%);"></div>
            </div>
        @else
            <div class="w-full h-72 sm:h-96 flex items-center justify-center text-6xl" style="background: linear-gradient(135deg, #1E6B2A, #6DBE4E);">
                ⚽
            </div>
        @endif

        {{-- Back button --}}
        <a href="{{ route('field') }}" wire:navigate
           class="absolute top-4 left-4 w-10 h-10 rounded-full bg-white/90 backdrop-blur-sm flex items-center justify-center shadow-lg hover:bg-white transition">
            <svg class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>

        {{-- Share button --}}
        <button class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/90 backdrop-blur-sm flex items-center justify-center shadow-lg hover:bg-white transition">
            <svg class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
            </svg>
        </button>
    </div>

    <div class="max-w-2xl mx-auto px-4 -mt-8 relative z-10">

        {{-- Main Info Card --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5 mb-4">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1 pr-4">
                    <span class="inline-block bg-green-50 text-green-700 text-xs font-bold px-2.5 py-1 rounded-full border border-green-100 mb-2">
                        Futsal Indoor
                    </span>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 leading-tight">{{ $field->name }}</h1>
                </div>
                <div class="flex-shrink-0 text-right">
                    <span class="text-yellow-500 font-extrabold text-lg">★ 4.8</span>
                    <p class="text-gray-400 text-xs">(128 ulasan)</p>
                </div>
            </div>

            <div class="flex items-center gap-2 text-gray-500 text-sm mb-4">
                <svg class="w-4 h-4 flex-shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="font-medium">{{ $field->address }}</span>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                <div>
                    <p class="text-xs text-gray-400 font-medium">Harga mulai</p>
                    <p class="font-extrabold text-xl" style="color: #3A9E3F;">
                        Rp {{ number_format($field->prices->min('price') ?? 0, 0, ',', '.') }}
                        <span class="text-sm font-normal text-gray-400">/jam</span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-100">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse inline-block"></span>
                        Tersedia
                    </span>
                </div>
            </div>
        </div>

        {{-- Gallery Grid --}}
        @if($mediaItems->count() > 1)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
            <h2 class="text-sm font-bold text-gray-700 mb-3">Foto Lapangan</h2>
            <div class="grid grid-cols-3 gap-2">
                @foreach($mediaItems->skip(1)->take(5) as $index => $media)
                    @if($index < 5)
                    <div class="relative overflow-hidden rounded-xl">
                        <img src="{{ $media->getUrl() }}"
                             class="w-full h-24 object-cover hover:scale-105 transition-transform duration-300"
                             alt="Foto lapangan">
                        @if($index == 4 && $mediaItems->count() > 6)
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center rounded-xl">
                                <span class="text-white font-bold text-sm">+{{ $mediaItems->count() - 6 }}</span>
                            </div>
                        @endif
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Description --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
            <h2 class="text-base font-extrabold text-gray-900 mb-3">Tentang Lapangan</h2>
            <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed">
                {!! $field->description ?? '<p class="text-gray-400 italic">Deskripsi belum ditambahkan.</p>' !!}
            </div>
        </div>

        {{-- Facilities --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
            <h2 class="text-base font-extrabold text-gray-900 mb-3">Fasilitas</h2>
            @if($field->facilities && count($field->facilities) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($field->facilities as $fasilitas)
                        <span class="flex items-center gap-1.5 bg-green-50 text-green-700 px-3 py-1.5 rounded-xl text-xs font-semibold border border-green-100">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $fasilitas }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400 italic text-sm">Data fasilitas belum ditambahkan.</p>
            @endif
        </div>

        {{-- CTA Button --}}
        <a href="{{ route('checkout', $field->id) }}" wire:navigate
           class="flex items-center justify-center gap-2 w-full py-4 rounded-2xl text-white font-extrabold text-base transition-all shadow-xl"
           style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E); box-shadow: 0 8px 24px rgba(58,158,63,0.4);">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Booking Lapangan Ini Sekarang
        </a>

    </div>
</div>