<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Booking;
use Midtrans\Config;
use Midtrans\Snap;

class Checkout extends Component
{
    public $field;
    public $bookingDate;
    public $selectedTimes = []; // Jam yang dipilih customer
    public $paymentType = 'full'; // 'dp' atau 'full'

    public function processBooking()
    {
        // 1. Hitung total harga berdasarkan jam yang dipilih
        $subtotal = $this->calculateSubtotal();
        
        // 2. Hitung jumlah yang harus dibayar (DP vs Full)
        $minDpPercent = $this->field->min_dp_percent;
        $amountToPay = $this->paymentType === 'dp' 
            ? ($subtotal * ($minDpPercent / 100)) 
            : $subtotal;

        // 3. Simpan ke database
        $booking = Booking::create([
            'user_id' => auth()->id(),
            'field_id' => $this->field->id,
            'booking_code' => 'PLG-' . time(),
            'booking_date' => $this->bookingDate,
            'subtotal' => $subtotal,
            'grand_total' => $subtotal,
            'payment_type' => $this->paymentType,
            'status' => 'pending',
        ]);

        // Simpan Booking Items (jam)
        // ... loop through $this->selectedTimes ...

        // 4. Integrasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $booking->booking_code,
                'gross_amount' => $amountToPay,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ]
        ];

        $snapToken = Snap::getSnapToken($params);
        $booking->update(['midtrans_snap_token' => $snapToken]);

        // Trigger event ke frontend untuk memunculkan popup Midtrans
        $this->dispatch('pay-with-midtrans', snapToken: $snapToken);
    }
}