<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Booking - PulseGo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white p-6 rounded-xl shadow-xl w-full max-w-sm text-center">
        <!-- Icon Validasi -->
        @if(in_array($booking->status, ['paid', 'dp_paid']))
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-green-600 mb-1">BOOKING VALID</h1>
        @else
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-red-600 mb-1">BELUM LUNAS / BATAL</h1>
        @endif

        <p class="text-gray-500 mb-6 font-medium">{{ $booking->booking_code }}</p>

        <div class="text-left space-y-3 border-t pt-4">
            <div>
                <p class="text-xs text-gray-500 uppercase">Customer</p>
                <p class="font-bold text-gray-800">{{ $booking->user ? $booking->user->name : 'Walk-in' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Jadwal Main</p>
                <p class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</p>
                <p class="text-sm text-gray-700 mt-1">
                    @foreach($booking->items as $item)
                    <span class="inline-block bg-gray-100 rounded px-2 py-1 mr-1 mb-1 border">
                        {{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }}
                    </span>
                    @endforeach
                </p>
            </div>
            <div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Status Pembayaran</p>
                    <p class="font-bold text-gray-800 uppercase">{{ str_replace('_', ' ', $booking->status) }}</p>

                    <!-- Box Peringatan Sisa Tagihan untuk Penjaga Lapangan -->
                    @if(in_array($booking->status, ['pending', 'dp_paid']) && $booking->payment_type == 'dp')
                    <div class="mt-2 bg-yellow-50 p-3 rounded-lg border border-yellow-200 text-sm">
                        <div class="flex justify-between mb-1">
                            <span class="text-gray-600">Total Harga:</span>
                            <span class="font-semibold text-gray-800">Rp {{ number_format($booking->grand_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between mb-1">
                            <span class="text-gray-600">DP Masuk:</span>
                            <span class="font-semibold text-green-600">Rp {{ number_format($booking->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-t border-yellow-300 mt-2 pt-2">
                            <span class="text-red-600 font-bold">Wajib Ditagih (Sisa):</span>
                            <span class="text-red-600 font-bold">Rp {{ number_format($booking->grand_total - $booking->paid_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @elseif($booking->status == 'paid')
                    <p class="text-sm text-green-600 font-semibold mt-1">LUNAS (Rp {{ number_format($booking->paid_amount, 0, ',', '.') }})</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

</body>

</html>