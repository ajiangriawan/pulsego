<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Booking - {{ $booking->booking_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body class="bg-gray-100 flex justify-center p-6">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md border border-gray-200">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-extrabold text-blue-600 tracking-tight">PulseGo</h1>
            <p class="text-gray-500 text-sm mt-1">Bukti Booking Lapangan Resmi</p>
        </div>

        <div class="border-t border-b border-gray-200 py-4 mb-4">
            <div class="flex justify-between mb-2">
                <span class="text-gray-600">Kode Booking:</span>
                <span class="font-bold">{{ $booking->booking_code }}</span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-gray-600">Tanggal Main:</span>
                <span class="font-bold">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-gray-600">Lapangan:</span>
                <span class="font-bold">{{ $booking->field->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Customer:</span>
                <span class="font-bold">{{ $booking->user ? $booking->user->name : 'Walk-in' }}</span>
            </div>
        </div>

        <div class="mb-4">
            <h3 class="font-semibold text-gray-700 mb-2">Jam Bermain:</h3>
            <ul class="text-sm text-gray-600 list-disc pl-5">
                @foreach($booking->items as $item)
                <li>{{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }}</li>
                @endforeach
            </ul>
        </div>

        <div class="bg-gray-50 p-4 rounded-md mb-6 border border-gray-200">
            <div class="flex justify-between mb-3 border-b border-gray-200 pb-2">
                <span class="text-gray-600">Status Pembayaran:</span>
                <span class="font-bold uppercase 
                    {{ $booking->status == 'paid' ? 'text-green-600' : ($booking->status == 'dp_paid' ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ str_replace('_', ' ', $booking->status) }}
                </span>
            </div>

            <!-- Rincian Biaya -->
            <div class="flex justify-between text-sm text-gray-600 mb-1">
                <span>Subtotal (Sewa Lapangan):</span>
                <span>Rp {{ number_format($booking->subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600 mb-2">
                <span>PPN (11%):</span>
                <span>Rp {{ number_format($booking->grand_total - $booking->subtotal, 0, ',', '.') }}</span>
            </div>

            <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                <span>Total Keseluruhan:</span>
                <span>Rp {{ number_format($booking->grand_total, 0, ',', '.') }}</span>
            </div>

            <!-- Rincian Jika Pembayaran adalah DP (30%) -->
            @if(in_array($booking->status, ['pending', 'dp_paid']) && $booking->payment_type == 'dp')
            <div class="flex justify-between text-sm text-gray-600 mt-2">
                <span>Telah Dibayar (DP 30%):</span>
                <span class="text-green-600 font-semibold">Rp {{ number_format($booking->paid_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-sm mt-2 pt-2 border-t border-dashed border-gray-300">
                <span class="font-bold text-red-600">Sisa Tagihan (Wajib Lunas):</span>
                <span class="font-bold text-red-600">Rp {{ number_format($booking->grand_total - $booking->paid_amount, 0, ',', '.') }}</span>
            </div>
            @endif

            <!-- Rincian Jika Sudah Lunas -->
            @if($booking->status == 'paid')
            <div class="flex justify-between text-sm mt-2 pt-2 border-t border-dashed border-gray-300">
                <span class="font-bold text-green-600">Lunas Dibayar:</span>
                <span class="font-bold text-green-600">Rp {{ number_format($booking->paid_amount, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>

        <div class="flex flex-col items-center justify-center text-center">
            <p class="text-sm text-gray-500 mb-3">Scan QR untuk validasi pesanan:</p>
            <div class="p-2 bg-white border-2 border-gray-200 rounded-lg shadow-sm">
                <!-- Generate QR Code mengarah ke halaman validasi -->
                {!! QrCode::size(150)->generate(route('booking.validate', $booking->booking_code)) !!}
            </div>
        </div>

        <div class="mt-8 text-center no-print">
            <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold shadow hover:bg-blue-700 transition">
                Print Struk
            </button>
        </div>
    </div>

</body>

</html>