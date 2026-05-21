<div class="min-h-screen bg-gray-100">

    {{-- ===== HEADER ===== --}}
    <div style="background: linear-gradient(135deg, #1E6B2A, #3A9E3F);">
        <div class="max-w-2xl mx-auto px-4 pt-8 pb-12 sm:pt-10">
            <div class="flex items-center gap-4 mb-1">
                <a href="{{ route('home') }}" wire:navigate
                   class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-white font-extrabold text-xl">Perlu Dibayar</h1>
                    <p class="text-green-200 text-xs">Semua aktivitas pemesanan Anda yang belum dibayar</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 -mt-6 pb-6">

        <!-- {{-- Stats Cards --}}
        <div class="grid grid-cols-3 gap-3 mb-6">
            @php
                $totalBookings = $bookings->count();
                $paidBookings = $bookings->whereIn('status', 'dp_paid')->count();
                $pendingBookings = $bookings->where('status', 'pending')->count();
            @endphp
            <div class="bg-white rounded-2xl p-4 text-center shadow-md border border-gray-100">
                <p class="text-2xl font-extrabold text-gray-900">{{ $totalBookings }}</p>
                <p class="text-[10px] font-semibold text-gray-400 mt-0.5">Total Booking</p>
            </div>
            <div class="bg-white rounded-2xl p-4 text-center shadow-md border border-green-100">
                <p class="text-2xl font-extrabold text-green-600">{{ $paidBookings }}</p>
                <p class="text-[10px] font-semibold text-gray-400 mt-0.5">Selesai</p>
            </div>
            <div class="bg-white rounded-2xl p-4 text-center shadow-md border border-yellow-100">
                <p class="text-2xl font-extrabold text-yellow-500">{{ $pendingBookings }}</p>
                <p class="text-[10px] font-semibold text-gray-400 mt-0.5">Menunggu</p>
            </div>
        </div> -->

        {{-- Error Alert --}}
        @if(session()->has('error'))
            <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 p-4 rounded-2xl mb-5 text-sm font-semibold shadow-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Booking List --}}
        <div class="space-y-4">
            @forelse($bookings as $booking)
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">

                {{-- Card Header --}}
                <div class="flex justify-between items-center px-5 py-4 border-b border-gray-100">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Kode Booking</p>
                        <h3 class="font-extrabold text-gray-900 text-sm tracking-wide">{{ $booking->booking_code }}</h3>
                    </div>
                    <div>
                        @php
                            $statusConfig = [
                                'paid'     => ['bg-green-100 text-green-700 border-green-200',  '✓ Lunas'],
                                'dp_paid'  => ['bg-yellow-100 text-yellow-700 border-yellow-200', '⟳ DP Dibayar'],
                                'pending'  => ['bg-blue-100 text-blue-700 border-blue-200',    '⏳ Menunggu'],
                                'cancelled'=> ['bg-red-100 text-red-700 border-red-200',       '✗ Dibatalkan'],
                            ];
                            $statusKey = $booking->status ?? 'pending';
                            [$statusClass, $statusLabel] = $statusConfig[$statusKey] ?? ['bg-gray-100 text-gray-700 border-gray-200', $statusKey];
                        @endphp
                        <span class="inline-block px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider border {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="px-5 py-4">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Lapangan</p>
                            <p class="font-extrabold text-gray-900 text-sm">{{ $booking->field->name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $booking->field->address ?? '' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tanggal Main</p>
                            <p class="font-extrabold text-gray-900 text-sm">
                                {{ \Carbon\Carbon::parse($booking->booking_date)->isoFormat('D MMM YYYY') }}
                            </p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Jam Bermain</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($booking->items as $item)
                                <span class="flex items-center gap-1 bg-green-50 border border-green-100 text-green-700 px-2.5 py-1 rounded-lg text-xs font-bold">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Payment Summary --}}
                    <div class="bg-gray-50 rounded-xl border border-gray-100 p-4 mb-4">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">Total Biaya (Inc. PPN)</span>
                                <span class="font-extrabold text-gray-900">Rp {{ number_format($booking->grand_total, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">Telah Dibayar</span>
                                <span class="font-extrabold text-green-600">Rp {{ number_format($booking->paid_amount, 0, ',', '.') }}</span>
                            </div>
                            @if($booking->status == 'dp_paid')
                                <div class="flex justify-between pt-2 border-t border-dashed border-gray-200">
                                    <span class="font-bold text-red-500">Sisa Tagihan (70%)</span>
                                    <span class="font-extrabold text-red-500">Rp {{ number_format($booking->grand_total - $booking->paid_amount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col sm:flex-row gap-2">
                        @if($booking->status == 'dp_paid' && $booking->payment_type == 'dp')
                            <button wire:click="payRemaining({{ $booking->id }})"
                                    wire:loading.attr="disabled"
                                    class="flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-white font-bold text-sm transition"
                                    style="background: linear-gradient(135deg, #DC2626, #EF4444); box-shadow: 0 4px 14px rgba(220,38,38,0.3);">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                Lunasi Sisa Tagihan
                            </button>

                        @elseif($booking->status == 'pending')
                            <a href="{{ $booking->midtrans_snap_token }}"
                               class="flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-white font-bold text-sm transition"
                               style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E); box-shadow: 0 4px 14px rgba(58,158,63,0.3);">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                Selesaikan Pembayaran
                            </a>

                        @else
                            <div class="flex items-center gap-2 py-2.5 px-4 rounded-xl bg-green-50 border border-green-100">
                                <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-xs font-bold text-green-700">Transaksi Selesai</span>
                            </div>
                        @endif

                        @if(in_array($booking->status, ['paid', 'dp_paid']))
                            <a href="{{ route('booking.receipt', $booking->booking_code) }}"
                               target="_blank"
                               class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs transition shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Cetak Tiket
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            @empty
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
                <div class="text-6xl mb-4">🏟️</div>
                <h3 class="font-extrabold text-gray-700 text-lg mb-2">Semua Booking Sudah Dibayar</h3>
                <p class="text-gray-400 text-sm mb-6">Yuk, booking lagi lapangan favoritmu sekarang!</p>
                <a href="{{ route('home') }}" wire:navigate
                   class="inline-flex items-center gap-2 py-3 px-6 rounded-2xl text-white font-bold text-sm transition"
                   style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E); box-shadow: 0 4px 14px rgba(58,158,63,0.3);">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Booking Sekarang
                </a>
            </div>
            @endforelse
        </div>

    </div>
</div>