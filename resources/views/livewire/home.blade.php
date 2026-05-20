<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="text-center mb-10">
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Selamat Datang di PulseGo</h1>
            <p class="mt-4 text-lg text-gray-500">Temukan dan booking lapangan olahraga terbaik di Palembang dengan mudah.</p>
        </div>

        <!-- Grid Lapangan -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($fields as $field)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition flex flex-col">
                <!-- Ambil foto pertama dari Spatie Media Library -->
                @if($field->getFirstMediaUrl('gallery'))
                <img src="{{ $field->getFirstMediaUrl('gallery') }}" alt="{{ $field->name }}" class="w-full h-48 object-cover">
                @else
                <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-400">
                    Tidak ada foto
                </div>
                @endif

                <div class="p-5 flex-1 flex flex-col">
                    <h3 class="text-xl font-bold text-gray-900">{{ $field->name }}</h3>

                    <!-- DESKRIPSI DIPOTONG (Maksimal 80 Karakter) -->
                    <p class="text-sm text-gray-500 mt-2 flex-1">
                        {{ \Illuminate\Support\Str::limit(strip_tags($field->description), 80, '...') }}
                    </p>

                    <div class="mt-5 pt-4 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">
                            Min. DP {{ $field->min_dp_percent }}%
                        </span>

                        <!-- Tombol menuju detail -->
                        <a href="{{ route('field.detail', $field->id) }}" wire:navigate class="text-sm font-semibold text-white bg-gray-900 hover:bg-gray-800 px-4 py-2 rounded-lg transition">
                            Lihat Detail
                        </a>
                        <!-- Tombol Lanjut Booking -->
                        <div class="mt-10 pt-6 border-t border-gray-200">
                            <a href="{{ route('checkout', $field->id) }}" wire:navigate class="...">
                                Booking Lapangan Ini Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center text-gray-500 py-10">
                Belum ada data lapangan yang tersedia.
            </div>
            @endforelse
        </div>

    </div>
</div>