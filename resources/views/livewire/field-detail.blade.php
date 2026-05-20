<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Tombol Kembali -->
        <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 mb-6">
            &larr; Kembali ke Daftar Lapangan
        </a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Galeri Foto (Grid Sederhana) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-1 p-1">
                @foreach($field->getMedia('gallery') as $media)
                <img src="{{ $media->getUrl() }}" class="w-full h-64 object-cover rounded-lg">
                @endforeach
            </div>

            <div class="p-6 md:p-8">
                <h1 class="text-3xl font-extrabold text-gray-900">{{ $field->name }}</h1>
                <p class="text-gray-500 mt-2">{{ $field->address }}</p>

                <div class="mt-6 prose max-w-none text-gray-700">
                    {!! $field->description !!}
                </div>

                <div class="mt-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Fasilitas Tersedia</h3>
                    <div class="flex flex-wrap gap-2">
                        @if($field->facilities)
                        @foreach($field->facilities as $fasilitas)
                        <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-medium border border-gray-200">
                            {{ $fasilitas }}
                        </span>
                        @endforeach
                        @else
                        <span class="text-gray-500 italic">Data fasilitas belum ditambahkan.</span>
                        @endif
                    </div>
                </div>

                <!-- Tombol Lanjut Booking -->
                <div class="mt-10 pt-6 border-t border-gray-200">
                    <a href="{{ route('checkout', $field->id) }}" wire:navigate class="...">
                        Booking Lapangan Ini Sekarang
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>