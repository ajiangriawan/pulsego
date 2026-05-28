<?php

namespace App\Livewire;

use App\Models\Booking;
use Carbon\Carbon;
use Livewire\Component;
use Midtrans\Config;
use Midtrans\Snap;

class BookingHistory extends Component
{
    // Variabel untuk Modal & Form Pembatalan
    public $isCancelModalOpen = false;
    public $cancelBookingId = null;
    public $expectedRefundAmount = 0;
    public $dpAmount = 0;
    
    // Variabel Input Form Refund
    public $refundBank;
    public $refundAccount;
    public $refundName;

    public function payRemaining($bookingId)
    {
        // ... (Kode payRemaining tetap sama seperti sebelumnya) ...
        $booking = Booking::where('user_id', auth()->id())->findOrFail($bookingId);

        if ($booking->status !== 'dp_paid' || $booking->payment_type !== 'dp') {
            session()->flash('error', 'Booking ini tidak memerlukan pelunasan.');
            return;
        }

        $remainingAmount = $booking->grand_total - $booking->paid_amount;

        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $orderId = $booking->booking_code . '-LNS-' . strtoupper(uniqid());

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => round($remainingAmount),
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'phone' => auth()->user()->phone ?? '081111111111',
            ]
        ];

        try {
            $paymentUrl = Snap::createTransaction($params)->redirect_url;
            $booking->update(['midtrans_snap_token' => $paymentUrl]);
            return redirect()->away($paymentUrl);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memproses pelunasan: ' . $e->getMessage());
        }
    }

    // Fungsi untuk membuka modal pembatalan dan menghitung simulasi refund
    public function promptCancel($bookingId)
    {
        $booking = Booking::with('field')->where('user_id', auth()->id())->findOrFail($bookingId);

        if ($booking->status === 'cancelled') {
            session()->flash('error', 'Pesanan ini sudah dibatalkan sebelumnya.');
            return;
        }

        // Cek minimal H-1
        $bookingDate = Carbon::parse($booking->booking_date)->startOfDay();
        $today = now()->startOfDay();
        
        if ($today->diffInDays($bookingDate, false) < 1) {
            session()->flash('error', 'Pembatalan ditolak. Pesanan hanya dapat dibatalkan maksimal 1 hari sebelum bermain.');
            return;
        }

        // Hitung estimasi refund
        $dpPercentage = $booking->field->min_dp_percent;
        $this->dpAmount = $booking->grand_total * ($dpPercentage / 100);
        $refundAmount = $booking->paid_amount - $this->dpAmount;
        
        $this->expectedRefundAmount = $refundAmount > 0 ? $refundAmount : 0;
        $this->cancelBookingId = $booking->id;
        
        // Kosongkan form rekening
        $this->resetValidation();
        $this->refundBank = '';
        $this->refundAccount = '';
        $this->refundName = '';

        $this->isCancelModalOpen = true; // Buka modal
    }

    // Fungsi untuk menutup modal
    public function closeCancelModal()
    {
        $this->isCancelModalOpen = false;
        $this->cancelBookingId = null;
    }

    // Fungsi untuk memproses data dari modal ke database
    public function processCancel()
    {
        // Validasi input rekening HANYA jika ada dana yang direfund
        if ($this->expectedRefundAmount > 0) {
            $this->validate([
                'refundBank' => 'required|string|max:50',
                'refundAccount' => 'required|numeric',
                'refundName' => 'required|string|max:100',
            ], [
                'refundBank.required' => 'Nama Bank wajib diisi.',
                'refundAccount.required' => 'Nomor Rekening wajib diisi.',
                'refundAccount.numeric' => 'Nomor Rekening harus berupa angka.',
                'refundName.required' => 'Nama Pemilik Rekening wajib diisi.',
            ]);
        }

        $booking = Booking::with('field')->findOrFail($this->cancelBookingId);

        // Update status menjadi cancelled dan simpan data rekening
        $booking->update([
            'status' => 'cancelled',
            'refund_bank' => $this->refundBank,
            'refund_account' => $this->refundAccount,
            'refund_name' => $this->refundName,
        ]);

        $dpPercentage = $booking->field->min_dp_percent;

        // Buat pesan respons
        if ($this->expectedRefundAmount > 0) {
            $message = 'Booking ' . $booking->booking_code . ' dibatalkan. Dana sebesar Rp ' . number_format($this->expectedRefundAmount, 0, ',', '.') . ' akan diproses ke rekening ' . $this->refundBank . ' Anda. (DP ' . floatval($dpPercentage) . '% hangus).';
        } else {
            $message = 'Booking ' . $booking->booking_code . ' berhasil dibatalkan. DP sebesar Rp ' . number_format($this->dpAmount, 0, ',', '.') . ' (' . floatval($dpPercentage) . '%) hangus.';
        }

        session()->flash('success', $message);
        
        $this->closeCancelModal();
    }

    public function render()
    {
        $bookings = Booking::with(['field', 'items'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('livewire.booking-history', compact('bookings'))->layout('layouts.app');
    }
}