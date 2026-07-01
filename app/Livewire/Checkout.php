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

    // === PERBAIKAN: LOGIKA VALIDASI PROMO DI WEB ===
    public function applyPromo()
    {
        $promo = Promo::where('code', $this->promoCode)->first();

        if (!$promo) {
            $this->appliedPromo = null;
            session()->flash('error_promo', 'Kode promo tidak ditemukan.');
            $this->calculateTotal();
            return;
        }

        // 1. Cek Batas Waktu
        if (Carbon::now()->startOfDay()->gt(Carbon::parse($promo->valid_until)->endOfDay())) {
            $this->appliedPromo = null;
            session()->flash('error_promo', 'Maaf, masa berlaku kode promo ini sudah habis.');
            $this->calculateTotal();
            return;
        }

        // 2. Cek apakah CUSTOMER INI sudah pernah memakai promo ini
        $hasUsedPromo = Booking::where('user_id', auth()->id())
            ->where('promo_id', $promo->id)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($hasUsedPromo) {
            $this->appliedPromo = null;
            session()->flash('error_promo', 'Anda sudah pernah menggunakan kode promo ini.');
            $this->calculateTotal();
            return;
        }

        // 3. Cek apakah BATAS MAKSIMAL (max_uses) GLOBAL sudah terpenuhi
        $totalUsage = Booking::where('promo_id', $promo->id)
            ->where('status', '!=', 'cancelled')
            ->count();

        if ($totalUsage >= $promo->max_uses) {
            $this->appliedPromo = null;
            session()->flash('error_promo', 'Maaf, kuota penggunaan kode promo ini sudah habis.');
            $this->calculateTotal();
            return;
        }

        // Jika lolos semua validasi
        $this->appliedPromo = $promo;
        session()->flash('success_promo', 'Promo berhasil digunakan!');
        
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
        
        // PPN 11% (Dibulatkan ke atas)
        $this->ppnAmount = ceil($afterDiscount * 0.11);
        $this->grandTotal = $afterDiscount + $this->ppnAmount;

        // Kalkulasi Minimal DP
        $minDpPercent = (float) ($this->field->min_dp_percent ?? 50.00);
        $this->dpAmount = ceil($this->grandTotal * ($minDpPercent / 100));
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
        
        if (!empty($this->promoCode) && empty($this->appliedPromo)) {
            session()->flash('error_promo', 'Silakan batalkan/hapus voucher yang tidak valid sebelum membayar.');
            return; // Hentikan proses!
        }

        // === DOUBLE CHECK PROMO SEBELUM CHECKOUT ===
        // Mencegah pelanggan yang mendiamkan halaman web terlalu lama
        if ($this->appliedPromo) {
            $hasUsedPromo = Booking::where('user_id', auth()->id())
                ->where('promo_id', $this->appliedPromo->id)
                ->where('status', '!=', 'cancelled')
                ->exists();

            $totalUsage = Booking::where('promo_id', $this->appliedPromo->id)
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($hasUsedPromo || $totalUsage >= $this->appliedPromo->max_uses) {
                $this->appliedPromo = null;
                $this->calculateTotal();
                session()->flash('error_promo', 'Gagal memproses: Kuota promo baru saja habis atau sudah Anda gunakan.');
                return; // Batalkan proses checkout
            }
        }

        $this->calculateTotal();

        // Nominal dinamis yang akan ditagihkan ke Midtrans
        $amountToPay = $this->paymentType === 'dp' ? $this->dpAmount : $this->grandTotal;

        // Amankan database menggunakan Transaction system
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

            // === PERBAIKAN CRITICAL TIMEOUT ===
            // Mengubah timeout menjadi 30 detik untuk menghindari error jaringan
            Config::$curlOptions = [
                CURLOPT_CONNECTTIMEOUT => 30, // Batas waktu bersalaman
                CURLOPT_TIMEOUT => 30,        // Batas waktu tunggu respon
                CURLOPT_HTTPHEADER => []      
            ];

            $params = [
                'transaction_details' => [
                    'order_id' => $booking->booking_code,
                    'gross_amount' => (int) round($amountToPay), // Konversi wajib tipe Integer murni
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'phone' => auth()->user()->phone ?? '081111111111',
                ]
            ];

            // 4. Buat Transaksi ke Midtrans
            $paymentUrl = Snap::createTransaction($params)->redirect_url;
            
            $booking->update(['midtrans_snap_token' => $paymentUrl]);

            // Jika sampai tahap ini sukses, kunci penyimpanan database
            \DB::commit();

            // Alihkan pelanggan ke portal pembayaran Midtrans
            return redirect()->away($paymentUrl);
            
        } catch (\Throwable $e) { 
            // Batalkan semua penyimpanan database jika di pertengahan ada yang gagal
            \DB::rollBack();
            
            // Tampilkan detail kegagalan murni ke layar agar bisa kita baca masalahnya
            dd('ERROR FATAL DITEMUKAN: ' . $e->getMessage() . ' | DI BARIS: ' . $e->getLine());
        }
    }

    public function render()
    {
        return view('livewire.checkout')->layout('layouts.app');
    }
}