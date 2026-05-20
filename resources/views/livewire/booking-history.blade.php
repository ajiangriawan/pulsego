<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

        <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Riwayat Booking Anda</h1>

        @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-600 p-4 rounded-lg mb-6 text-sm font-medium">
            {{ session('error') }}
        </div>
        @endif

        <div class="space-y-6">
            @forelse($bookings as $booking)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden p-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b pb-4 mb-4 gap-2">
                    <div>
                        <span class="text-xs font-mono text-gray-400">KODE BOOKING</span>
                        <h3 class="text-lg font-bold text-gray-900">{{ $booking->booking_code }}</h3>
                    </div>

                    <div>
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                {{ $booking->status == 'paid' ? 'bg-green-100 text-green-800' : 
                                   ($booking->status == 'dp_paid' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($booking->status == 'pending' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')) }}">
                            {{ str_replace('_', ' ', $booking->status) }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 text-sm">
                    <div>
                        <p class="text-gray-500">Lapangan</p>
                        <p class="font-bold text-gray-800 text-base">{{ $booking->field->name }}</p>
                        <p class="text-gray-500 mt-2">Tanggal Main</p>
                        <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Jam Bermain</p>
                        <div class="flex flex-wrap gap-1.5 mt-1">
                            @foreach($booking->items as $item)
                            <span class="bg-gray-100 border text-gray-700 px-2 py-0.5 rounded text-xs font-medium">
                                {{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 border flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="space-y-1 text-sm w-full md:w-auto">
                        <div class="flex justify-between md:block gap-4">
                            <span class="text-gray-500">Total Biaya (Inc. PPN):</span>
                            <span class="font-bold text-gray-900 md:ml-2">Rp {{ number_format($booking->grand_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between md:block gap-4">
                            <span class="text-gray-500">Telah Dibayar:</span>
                            <span class="font-semibold text-green-600 md:ml-2">Rp {{ number_format($booking->paid_amount, 0, ',', '.') }}</span>
                        </div>

                        @if($booking->status == 'dp_paid')
                        <div class="flex justify-between md:block gap-4 pt-1 border-t border-dashed border-gray-300 md:border-none">
                            <span class="font-bold text-red-600">Sisa Tagihan (70%):</span>
                            <span class="font-extrabold text-red-600 md:ml-2">Rp {{ number_format($booking->grand_total - $booking->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="w-full md:w-auto flex flex-col gap-2 mt-4 md:mt-0 text-right">

                        @if($booking->status == 'dp_paid' && $booking->payment_type == 'dp')
                        <button wire:click="payRemaining({{ $booking->id }})" wire:loading.attr="disabled" class="w-full md:w-auto bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-5 rounded-lg shadow text-sm transition text-center">
                            Pelunasan Sisa Tagihan
                        </button>
                        @elseif($booking->status == 'pending')
                        <a href="{{ $booking->midtrans_snap_token }}" class="inline-block text-center w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-lg shadow text-sm transition">
                            Lanjutkan Pembayaran Awal
                        </a>
                        @else
                        <div class="text-right">
                            <span class="text-xs text-green-600 bg-green-50 px-3 py-1.5 rounded-lg font-bold inline-block border border-green-200">
                                &check; Transaksi Selesai
                            </span>
                        </div>
                        @endif

                        @if(in_array($booking->status, ['paid', 'dp_paid']))
                        <a href="{{ route('booking.receipt', $booking->booking_code) }}" target="_blank" class="inline-flex items-center justify-center gap-2 w-full md:w-auto bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded-lg shadow text-xs transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Lihat & Cetak Tiket
                        </a>
                        @endif

                    </div>
                </div>

            </div>
            @empty
            <div class="bg-white rounded-xl border p-12 text-center text-gray-500 shadow-sm">
                Anda belum pernah melakukan booking lapangan.
            </div>
            @endforelse
        </div>

    </div>
</div>