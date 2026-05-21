<?php

namespace App\Livewire;

use App\Models\Booking;
use Livewire\Component;
use Midtrans\Config;
use Midtrans\Snap;

class BookingHistoryPayment extends Component
{
    public function payRemaining($bookingId)
    {
        $booking = Booking::where('user_id', auth()->id())->findOrFail($bookingId);

        // Pastikan pesanan memang berstatus DP Terbayar dan tipenya DP
        if ($booking->status !== 'dp_paid' || $booking->payment_type !== 'dp') {
            session()->flash('error', 'Booking ini tidak memerlukan pelunasan.');
            return;
        }

        // Hitung sisa tagihan secara matematis (Total dikurangi DP yang sudah masuk)
        $remainingAmount = $booking->grand_total - $booking->paid_amount;

        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // CATATAN: Midtrans melarang Order ID duplikat.
        // Kita buat Order ID baru khusus pelunasan dengan menambahkan suffix '-LNS'
        $orderId = $booking->booking_code . '-LNS-' . strtoupper(uniqid());

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => round($remainingAmount), // Mengirimkan sisa nominal 70%
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'phone' => auth()->user()->phone ?? '081111111111',
            ]
        ];

        try {
            // Dapatkan URL Redirect pelunasan dari Midtrans
            $paymentUrl = Snap::createTransaction($params)->redirect_url;
            
            // Perbarui tautan token di database agar mengarah ke link pelunasan terbaru
            $booking->update(['midtrans_snap_token' => $paymentUrl]);

            // Alihkan customer langsung ke halaman pembayaran Midtrans
            return redirect()->away($paymentUrl);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memproses pelunasan ke Midtrans: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Tarik semua data booking milik user yang sedang login
        $bookings = Booking::with(['field', 'items'])
            ->where('user_id', auth()->id() && 'status', ['dp_paid','pending'])
            ->latest()
            ->get();

        return view('livewire.booking-history-payment', compact('bookings'))->layout('layouts.app');
    }
}
