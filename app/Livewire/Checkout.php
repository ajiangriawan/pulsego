<?php

namespace App\Livewire;

use App\Models\Field;
use App\Models\FieldPrice;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Promo;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Midtrans\Config;
use Midtrans\Snap;
use Exception;

class Checkout extends Component
{
    public $field;
    public $bookingDate;
    public $selectedTimes = [];
    public $paymentType = 'full'; // Default bayar lunas
    
    // Promo
    public $promoCode = '';
    public $appliedPromo = null;

    // Kalkulasi
    public $subtotal = 0;
    public $discountAmount = 0;
    public $ppnAmount = 0;
    public $grandTotal = 0;
    public $dpAmount = 0;

    public function mount($id)
    {
        $this->field = Field::findOrFail($id);
        $this->bookingDate = Carbon::today()->format('Y-m-d');
        $this->calculateTotal();
    }

    // Mengambil jam dari database sesuai hari yang dipilih
    #[Computed]
    public function availableTimes()
    {
        if (!$this->bookingDate) return [];

        $dayOfWeek = Carbon::parse($this->bookingDate)->format('l');
        $prices = FieldPrice::where('field_id', $this->field->id)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')->get();

        $bookedTimes = BookingItem::whereHas('booking', function ($q) {
            $q->where('field_id', $this->field->id)
              ->whereDate('booking_date', $this->bookingDate)
              ->whereIn('status', ['pending', 'dp_paid', 'paid']);
        })->pluck('start_time')->map(fn($t) => Carbon::parse($t)->format('H:i:s'))->toArray();

        $times = [];
        foreach ($prices as $price) {
            $dbStart = Carbon::parse($price->start_time)->format('H:i:s');
            $times[] = [
                'start' => Carbon::parse($price->start_time)->format('H:i'),
                'end' => Carbon::parse($price->end_time)->format('H:i'),
                'price' => $price->price,
                'is_booked' => in_array($dbStart, $bookedTimes),
                'value' => "{$dbStart}|{$price->end_time}|{$price->price}"
            ];
        }
        return $times;
    }

    public function updatedBookingDate()
    {
        $this->selectedTimes = [];
        $this->calculateTotal();
    }

    public function updatedSelectedTimes()
    {
        $this->calculateTotal();
    }

    public function updatedPaymentType()
    {
        $this->calculateTotal();
    }

    public function applyPromo()
    {
        // Pastikan input promo berupa string (karena bisa terkirim null dari Alpine.js)
        $this->promoCode = (string) $this->promoCode;

        if (empty($this->promoCode)) {
            $this->appliedPromo = null;
            $this->calculateTotal();
            return;
        }

        $promo = Promo::where('code', $this->promoCode)
            ->whereDate('valid_until', '>=', Carbon::today())
            ->first();

        if ($promo) {
            $this->appliedPromo = $promo;
            session()->flash('success_promo', 'Promo berhasil digunakan!');
        } else {
            $this->appliedPromo = null;
            session()->flash('error_promo', 'Kode promo tidak valid atau kadaluarsa.');
        }
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->subtotal = 0;
        foreach ($this->selectedTimes as $val) {
            $parts = explode('|', $val);
            if (isset($parts[2])) {
                $this->subtotal += (float) $parts[2];
            }
        }

        // Hitung Diskon
        $this->discountAmount = 0;
        if ($this->appliedPromo) {
            if ($this->appliedPromo->discount_type === 'percent') {
                $this->discountAmount = $this->subtotal * ($this->appliedPromo->discount_amount / 100);
            } else {
                $this->discountAmount = $this->appliedPromo->discount_amount;
            }
        }

        $afterDiscount = max(0, $this->subtotal - $this->discountAmount);
        
        // === PERBAIKAN 1: PPN 11% (Dibulatkan ke atas agar tidak ada masalah koma desimal)
        $this->ppnAmount = ceil($afterDiscount * 0.11);
        $this->grandTotal = $afterDiscount + $this->ppnAmount;

        // KALKULASI MINIMAL DP MENGIKUTI ATURAN BARIS LAPANGAN
        $minDpPercent = (float) ($this->field->min_dp_percent ?? 50.00);
        $this->dpAmount = ceil($this->grandTotal * ($minDpPercent / 100)); // Dibulatkan ke atas juga
    }

    public function processPayment()
    {
        $this->validate([
            'bookingDate' => 'required|date|after_or_equal:today',
            'selectedTimes' => 'required|array|min:1',
            'paymentType' => 'required|in:dp,full',
        ], [
            'selectedTimes.required' => 'Pilih minimal 1 jam bermain.',
        ]);

        // Recalculate untuk memastikan data aman sebelum dikirim ke payment gateway
        $this->calculateTotal();

        // Nominal dinamis yang akan ditagihkan ke Midtrans
        $amountToPay = $this->paymentType === 'dp' ? $this->dpAmount : $this->grandTotal;

        // === PERBAIKAN 2: Gunakan \DB::beginTransaction() untuk melindungi data gantung
        \DB::beginTransaction();

        try {
            // 1. Simpan Data Booking Utama
            $booking = Booking::create([
                'user_id' => auth()->id(),
                'field_id' => $this->field->id,
                'promo_id' => $this->appliedPromo ? $this->appliedPromo->id : null,
                'booking_code' => 'PLG-' . strtoupper(uniqid()),
                'booking_date' => $this->bookingDate,
                'subtotal' => $this->subtotal,
                'discount' => $this->discountAmount,
                'tax' => $this->ppnAmount, // PENTING: Tambahkan ini jika di tabelmu ada kolom tax (sebaiknya ada)
                'grand_total' => $this->grandTotal,
                'paid_amount' => 0,
                'payment_type' => $this->paymentType,
                'status' => 'pending',
            ]);

            // 2. Simpan Item Jam Bermain yang Diambil
            foreach ($this->selectedTimes as $timeStr) {
                $parts = explode('|', $timeStr);
                BookingItem::create([
                    'booking_id' => $booking->id,
                    'start_time' => $parts[0],
                    'end_time'   => $parts[1],
                    'price'      => $parts[2],
                ]);
            }

            // 3. Konfigurasi Transaksi Jaringan Midtrans
            Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $booking->booking_code,
                    'gross_amount' => (int) round($amountToPay), // Pastikan menjadi Integer murni
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'phone' => auth()->user()->phone ?? '081111111111',
                ]
            ];

            // MENGGUNAKAN SNAP REDIRECT
            $paymentUrl = Snap::createTransaction($params)->redirect_url;
            
            $booking->update(['midtrans_snap_token' => $paymentUrl]);

            // Jika Midtrans berhasil, kunci data di database
            \DB::commit();

            // Alihkan pelanggan langsung ke halaman aman instan Midtrans
            return redirect()->to($paymentUrl);
            
        } catch (Exception $e) {
            // Jika GAGAL, hapus data pesanan dari database (Rollback) agar tidak menumpuk
            \DB::rollBack();
            
            // Tampilkan pesan error di layar pengguna (bukan halaman blank)
            session()->flash('error_payment', 'Gagal terhubung dengan sistem pembayaran: ' . $e->getMessage());
            return;
        }
    }

    public function render()
    {
        return view('livewire.checkout')->layout('layouts.app');
    }
}