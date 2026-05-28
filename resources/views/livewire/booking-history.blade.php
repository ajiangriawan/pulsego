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
                    <h1 class="text-white font-extrabold text-xl">Riwayat Booking</h1>
                    <p class="text-green-200 text-xs">Semua aktivitas pemesanan Anda</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 -mt-6 pb-6">

        {{-- Stats Cards --}}
        <div class="grid grid-cols-3 gap-3 mb-6">
            @php
            $totalBookings = $bookings->count();
            $paidBookings = $bookings->whereIn('status', ['paid', 'dp_paid'])->count();
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
        </div>

        {{-- Error & Success Alerts --}}
        @if(session()->has('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 p-4 rounded-2xl mb-5 text-sm font-semibold shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            {{ session('error') }}
        </div>
        @endif

        @if(session()->has('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 p-4 rounded-2xl mb-5 text-sm font-semibold shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Booking List --}}
        <div class="space-y-4">
            @forelse($bookings as $booking)
            
            {{-- Tambahkan x-data="{ expanded: false }" untuk Alpine.js --}}
            <div x-data="{ expanded: false }" class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">

                {{-- Card Header (Clickable) --}}
                <div @click="expanded = !expanded" class="cursor-pointer flex justify-between items-center px-5 py-4 border-b border-gray-100 hover:bg-gray-50 transition select-none">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-0.5">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kode</p>
                            <h3 class="font-extrabold text-gray-900 text-sm tracking-wide">{{ $booking->booking_code }}</h3>
                        </div>
                        {{-- Menampilkan ringkasan lapangan & tanggal saat ditutup --}}
                        <p class="text-xs text-gray-500 font-medium truncate">
                            {{ $booking->field->name }} • {{ \Carbon\Carbon::parse($booking->booking_date)->isoFormat('D MMM YYYY') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        @php
                        $statusConfig = [
                        'paid' => ['bg-green-100 text-green-700 border-green-200', '✓ Lunas'],
                        'dp_paid' => ['bg-yellow-100 text-yellow-700 border-yellow-200', '⟳ DP Dibayar'],
                        'pending' => ['bg-blue-100 text-blue-700 border-blue-200', '⏳ Menunggu'],
                        'cancelled'=> ['bg-red-100 text-red-700 border-red-200', '✗ Dibatalkan'],
                        ];
                        $statusKey = $booking->status ?? 'pending';
                        [$statusClass, $statusLabel] = $statusConfig[$statusKey] ?? ['bg-gray-100 text-gray-700 border-gray-200', $statusKey];
                        @endphp
                        <span class="inline-block px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider border {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                        
                        {{-- Icon Chevron (Panah) yang berputar saat diklik --}}
                        <svg :class="{'rotate-180': expanded}" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                {{-- Card Body (Akan muncul saat di klik) --}}
                <div x-show="expanded" x-collapse x-cloak style="display: none;">
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
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                                    <span class="font-bold text-red-500">Sisa Tagihan ({{ floatval(100 - $booking->field->min_dp_percent) }}%)</span>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                Lunasi Sisa Tagihan
                            </button>

                            @elseif($booking->status == 'pending')
                            <a href="{{ $booking->midtrans_snap_token }}"
                                class="flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-white font-bold text-sm transition"
                                style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E); box-shadow: 0 4px 14px rgba(58,158,63,0.3);">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                Selesaikan Pembayaran
                            </a>

                            @elseif($booking->status == 'cancelled')
                            <div class="flex items-center gap-2 py-2.5 px-4 rounded-xl bg-red-50 border border-red-100 flex-1">
                                <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="text-xs font-bold text-red-700">Dibatalkan</span>
                            </div>

                            @else
                            <div class="flex items-center gap-2 py-2.5 px-4 rounded-xl bg-green-50 border border-green-100 flex-1">
                                <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
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

                            @php
                            $isCancellable = $booking->status !== 'cancelled' && \Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($booking->booking_date)->startOfDay(), false) >= 1;
                            @endphp

                            @if($isCancellable)
                            <button wire:click="promptCancel({{ $booking->id }})"
                                class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 hover:border-red-300 font-bold text-xs transition shadow-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Batalkan
                            </button>
                            @endif

                        </div>
                    </div>
                </div>
            </div>

            @empty
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
                <div class="text-6xl mb-4">🏟️</div>
                <h3 class="font-extrabold text-gray-700 text-lg mb-2">Belum Ada Booking</h3>
                <p class="text-gray-400 text-sm mb-6">Yuk, mulai booking lapangan favoritmu sekarang!</p>
                <a href="{{ route('home') }}" wire:navigate
                    class="inline-flex items-center gap-2 py-3 px-6 rounded-2xl text-white font-bold text-sm transition"
                    style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E); box-shadow: 0 4px 14px rgba(58,158,63,0.3);">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Booking Sekarang
                </a>
            </div>
            @endforelse
        </div>

        {{-- MODAL PEMBATALAN --}}
        @if($isCancelModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 transition-opacity">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden">
                {{-- Modal Header --}}
                <div class="bg-red-50 p-5 border-b border-red-100 flex items-center gap-3">
                    <div class="bg-red-100 text-red-600 p-2 rounded-full">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-red-700 text-lg">Batalkan Booking</h3>
                        <p class="text-xs text-red-500 font-semibold mt-0.5">Tindakan ini tidak dapat diubah</p>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="p-5">
                    <div class="mb-5 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 font-medium">Uang DP (Hangus):</span>
                            <span class="font-extrabold text-red-600">Rp {{ number_format($dpAmount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm pt-2 border-t border-gray-100">
                            <span class="text-gray-900 font-bold">Estimasi Refund:</span>
                            <span class="font-extrabold text-green-600">Rp {{ number_format($expectedRefundAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Form Rekening (Hanya muncul jika ada dana yang direfund) --}}
                    @if($expectedRefundAmount > 0)
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-4">
                        <p class="text-xs text-blue-700 font-medium mb-3">
                            Silakan isi detail rekening Anda untuk proses pengembalian sisa dana. Dana akan dikirim maksimal 2x24 jam kerja.
                        </p>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Nama Bank (Misal: BCA, Mandiri)</label>
                                <input type="text" wire:model="refundBank" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                @error('refundBank') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Nomor Rekening</label>
                                <input type="text" wire:model="refundAccount" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                @error('refundAccount') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Atas Nama</label>
                                <input type="text" wire:model="refundName" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                @error('refundName') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-orange-50 border border-orange-100 p-3 rounded-xl mb-4">
                        <p class="text-xs text-orange-700 font-semibold text-center">
                            Anda baru melakukan pembayaran DP. Seluruh dana yang masuk (DP) akan hangus sesuai dengan kebijakan pembatalan.
                        </p>
                    </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="p-5 border-t border-gray-100 flex gap-3">
                    <button wire:click="closeCancelModal"
                        class="flex-1 py-2.5 px-4 rounded-xl border border-gray-200 text-gray-600 font-bold text-sm hover:bg-gray-50 transition">
                        Kembali
                    </button>
                    <button wire:click="processCancel" wire:loading.attr="disabled"
                        class="flex-1 py-2.5 px-4 rounded-xl text-white font-bold text-sm transition"
                        style="background: linear-gradient(135deg, #DC2626, #EF4444); box-shadow: 0 4px 14px rgba(220,38,38,0.3);">
                        Ya, Batalkan
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>