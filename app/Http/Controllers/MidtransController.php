<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function callback(Request $request)
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);

        // Validasi Signature Key (Keamanan agar tidak ada yang memalsukan request)
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $signatureKey = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($signatureKey !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $status = $request->transaction_status;
        $orderId = $request->order_id;
        $grossAmount = $request->gross_amount;

        // Deteksi apakah ini order DP awal atau Pelunasan Sisa Tagihan (-LNS-)
        $isPelunasan = str_contains($orderId, '-LNS-');
        
        if ($isPelunasan) {
            // Ambil kode booking aslinya (misal dari PLG-XXXX-LNS-YYYY menjadi PLG-XXXX)
            $baseBookingCode = explode('-LNS-', $orderId)[0];
            $booking = Booking::where('booking_code', $baseBookingCode)->first();
        } else {
            $booking = Booking::where('booking_code', $orderId)->first();
        }

        if (!$booking) {
            return response()->json(['message' => 'Booking tidak ditemukan'], 404);
        }

        // --- LOGIKA UPDATE STATUS ---
        if ($status == 'capture' || $status == 'settlement') {
            
            if ($isPelunasan) {
                // Jika pelunasan berhasil, ubah jadi Full Paid dan total bayar menjadi 100%
                $booking->update([
                    'status' => 'paid',
                    'paid_amount' => $booking->grand_total
                ]);
            } else {
                // Jika pembayaran awal
                if ($booking->payment_type == 'dp') {
                    $booking->update([
                        'status' => 'dp_paid',
                        'paid_amount' => $grossAmount
                    ]);
                } else {
                    $booking->update([
                        'status' => 'paid',
                        'paid_amount' => $booking->grand_total
                    ]);
                }
            }

        } elseif ($status == 'cancel' || $status == 'deny' || $status == 'expire') {
            // Jika pembayaran gagal/expired, batalkan pesanan
            // TAPI, jika yang gagal adalah proses pelunasannya, jangan batalkan pesanannya (tetap dp_paid)
            if (!$isPelunasan) {
                $booking->update([
                    'status' => 'cancelled'
                ]);
            }
        }

        return response()->json(['message' => 'Callback diproses berhasil']);
    }
}