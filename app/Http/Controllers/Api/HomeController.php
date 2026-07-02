<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Field;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class HomeController extends Controller
{
    public function getHomeData(Request $request)
    {
        // 1. Tangkap parameter koordinat GPS yang dikirim oleh HP
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        // Mulai query dasar dengan relasi tabel harga
        $query = Field::with('prices');

        // 2. Jika HP mengirimkan lokasi, hitung jarak menggunakan Rumus Haversine
        if ($lat && $lng) {
            $query->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance_km", [$lat, $lng, $lat])
                ->orderBy('distance_km', 'asc'); // Urutkan dari yang paling dekat
        } else {
            // Jika GPS HP mati, urutkan berdasarkan data terbaru saja
            $query->latest();
        }

        // Ambil maksimal 5 data lapangan
        $fields = $query->take(2)->get()->map(function ($field) use ($lat, $lng) {
            $price = $field->prices->first() ? $field->prices->first()->price : 0;

            // 3. Format teks jarak agar rapi saat dibaca di aplikasi mobile
            $distanceText = null;
            if ($lat && $lng && isset($field->distance_km)) {
                // Jika jarak di bawah 1 KM, ubah ke satuan meter. Jika di atas, gunakan KM.
                $distanceText = $field->distance_km < 1
                    ? number_format($field->distance_km * 1000, 0) . " meter"
                    : number_format($field->distance_km, 1) . " km";
            }

            return [
                'id' => $field->id,
                'name' => $field->name,
                'type' => $field->type ?? 'Olahraga',
                'address' => $field->address,
                'min_dp_percent' => (float) $field->min_dp_percent,
                'image_url' => $field->getFirstMediaUrl('gallery') ?: null,
                'price_formatted' => number_format($price, 0, ',', '.'),
                'rating' => '4.8',

                // KUNCI UTAMA: Mengirimkan teks jarak hasil hitungan ke React Native
                'distance' => $distanceText
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data beranda berhasil diambil',
            'data' => [
                'popular_fields' => $fields
            ]
        ], 200);
    }


    public function getFieldDetail($id)
    {
        $field = Field::with(['prices', 'media'])->find($id);

        if (!$field) {
            return response()->json(['success' => false, 'message' => 'Lapangan tidak ditemukan'], 404);
        }

        $price = $field->prices->first() ? $field->prices->first()->price : 0;

        // === LOGIKA BARU: AMBIL SEMUA FOTO ===
        $mediaItems = $field->getMedia('gallery');
        $images = [];

        foreach ($mediaItems as $item) {
            $rawUrl = $item->getUrl();
            if (str_starts_with($rawUrl, 'http')) {
                $images[] = $rawUrl;
            } else {
                // Pastikan APP_URL di .env menggunakan Ngrok
                $images[] = config('app.url') . $rawUrl;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $field->id,
                'name' => $field->name,
                'type' => $field->type,
                'address' => $field->address,
                'description' => $field->description ?? 'Lapangan olahraga berkualitas dengan fasilitas standar nasional.',
                'price_formatted' => number_format($price, 0, ',', '.'),

                // Kirim array gambar, bukan cuma satu
                'images' => $images,

                'facilities' => ['Parkir Luas', 'Kantin', 'Toilet & Ruang Ganti', 'Musholla'],
                'min_dp_percent' => $field->min_dp_percent,
            ]
        ]);
    }

    public function getAllFields(Request $request)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $search = $request->query('search');
        $category = $request->query('category');
        $sortBy = $request->query('sortBy', 'latest'); // Default ke latest

        $query = Field::with('prices');

        // 1. Filter Pencarian Teks
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%');
            });
        }

        // 2. Filter Kategori
        if ($category && $category !== 'Semua' && $category !== '') {
            $query->where('type', $category);
        }

        // 3. Logika Sorting (Sama persis dengan Livewire)
        if ($sortBy === 'price_asc') {
            $query->orderByRaw('(SELECT COALESCE(MIN(price), 0) FROM field_prices WHERE field_prices.field_id = fields.id) ASC');
        } elseif ($sortBy === 'price_desc') {
            $query->orderByRaw('(SELECT COALESCE(MIN(price), 0) FROM field_prices WHERE field_prices.field_id = fields.id) DESC');
        } elseif ($sortBy === 'name') {
            $query->orderBy('name', 'asc');
        } else {
            // Jika default ('latest'), utamakan jarak terdekat jika GPS aktif
            if ($lat && $lng) {
                $query->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance_km", [$lat, $lng, $lat])
                    ->orderBy('distance_km', 'asc');
            } else {
                $query->latest();
            }
        }

        $fields = $query->get()->map(function ($field) use ($lat, $lng) {
            $price = $field->prices->first() ? $field->prices->first()->price : 0;

            $distanceText = null;
            if ($lat && $lng && isset($field->distance_km)) {
                $distanceText = $field->distance_km < 1
                    ? number_format($field->distance_km * 1000, 0) . " meter"
                    : number_format($field->distance_km, 1) . " km";
            }

            $rawImageUrl = $field->getFirstMediaUrl('gallery');
            $finalImageUrl = null;
            if ($rawImageUrl) {
                $finalImageUrl = str_starts_with($rawImageUrl, 'http') ? $rawImageUrl : config('app.url') . $rawImageUrl;
            }

            return [
                'id' => $field->id,
                'name' => $field->name,
                'type' => $field->type ?? 'Olahraga',
                'address' => $field->address,
                'price_formatted' => number_format($price, 0, ',', '.'),
                'image_url' => $finalImageUrl,
                'rating' => '4.8',
                'distance' => $distanceText
            ];
        });

        // 4. Mengambil Kategori Dinamis dari Database (Sama seperti Web)
        $types = Field::select('type')
            ->distinct()
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->pluck('type');

        return response()->json([
            'success' => true,
            'data' => $fields,
            'categories' => $types // Kirim array kategori ke Mobile
        ]);
    }

    // === FUNGSI PEMBATALAN PESANAN (MOBILE) ===
    public function getHistory(Request $request)
    {
        $user = $request->user();

        $orders = Booking::with(['field', 'items'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($order) {
                $statusColor = '#ca8a04';
                $statusBg = '#fef3c7';

                if ($order->status === 'paid' || $order->status === 'dp_paid') {
                    $statusColor = '#16a34a';
                    $statusBg = '#dcfce7';
                } elseif ($order->status === 'cancelled' || $order->status === 'expired') {
                    $statusColor = '#dc2626';
                    $statusBg = '#fee2e2';
                }

                // Kalkulasi Aturan H-1 untuk Tombol Batalkan di HP
                $today = \Carbon\Carbon::now()->startOfDay();
                $bookingDate = \Carbon\Carbon::parse($order->booking_date)->startOfDay();
                $isCancellable = $order->status !== 'cancelled' && $today->diffInDays($bookingDate, false) >= 1;

                return [
                    'id' => $order->id,
                    'invoice' => $order->booking_code,
                    'field_name' => $order->field ? $order->field->name : 'Lapangan Dihapus',
                    'type' => $order->field ? $order->field->type : 'Olahraga',
                    'play_date' => \Carbon\Carbon::parse($order->booking_date)->translatedFormat('d M Y'),
                    'time' => $order->items->map(function ($item) {
                        return \Carbon\Carbon::parse($item->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($item->end_time)->format('H:i');
                    })->implode(', ') . ' WIB',
                    'status' => strtoupper(str_replace('_', ' ', $order->status)),
                    'status_color' => $statusColor,
                    'status_bg' => $statusBg,
                    'is_need_repayment' => ($order->status === 'dp_paid' && $order->payment_type === 'dp'),
                    'payment_url' => $order->midtrans_snap_token,

                    // Data Mentah & Status Pembatalan untuk kalkulasi Modal di React Native
                    'is_cancellable' => $isCancellable,
                    'min_dp_percent' => $order->field ? (float) $order->field->min_dp_percent : 50,
                    'raw_total_price' => $order->grand_total,
                    'raw_paid_amount' => $order->paid_amount,

                    // Tampilan Text
                    'paid_amount' => number_format($order->paid_amount, 0, ',', '.'),
                    'total_price' => number_format($order->grand_total, 0, ',', '.'),
                    'remaining_amount' => number_format($order->grand_total - $order->paid_amount, 0, ',', '.'),

                    'image_url' => $order->field && $order->field->getFirstMediaUrl('gallery')
                        ? $order->field->getFirstMediaUrl('gallery')
                        : 'https://ui-avatars.com/api/?name=Field',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    // === 2. PROSES PELUNASAN (PAY REMAINING) ===
    public function payRemaining(Request $request, $id)
    {
        $user = $request->user();

        // Cari booking milik user yang sedang login
        $booking = Booking::where('user_id', $user->id)->find($id);

        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        // Pastikan pesanan memang berstatus DP Terbayar
        if ($booking->status !== 'dp_paid' || $booking->payment_type !== 'dp') {
            return response()->json(['success' => false, 'message' => 'Booking ini tidak memerlukan pelunasan.'], 400);
        }

        $remainingAmount = $booking->grand_total - $booking->paid_amount;

        // Konfigurasi Midtrans
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
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '081111111111',
            ],
            'callbacks' => [
                'finish' => 'pulsego://history',
                'error' => 'pulsego://history',
                'pending' => 'pulsego://history'
            ]
        ];

        try {
            // Dapatkan URL dari Midtrans
            $paymentUrl = Snap::createTransaction($params)->redirect_url;

            // Simpan link pelunasan terbaru ke database
            $booking->update(['midtrans_snap_token' => $paymentUrl]);

            // Kembalikan Link ke Mobile App agar dibuka di HP (via Linking/WebView)
            return response()->json([
                'success' => true,
                'payment_url' => $paymentUrl
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pelunasan: ' . $e->getMessage()
            ], 500);
        }
    }

    // === MENGAMBIL JADWAL BERDASARKAN TANGGAL TERTENTU ===
    public function getAvailableTimes(Request $request, $field_id)
    {
        $date = $request->query('date', now()->format('Y-m-d'));
        $dayOfWeek = \Carbon\Carbon::parse($date)->format('l');

        // Ambil master harga hari itu dari database
        $prices = \App\Models\FieldPrice::where('field_id', $field_id)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')->get();

        // Cari jam berapa saja yang SUDAH di-booking pada tanggal tersebut
        $bookedTimes = \App\Models\BookingItem::whereHas('booking', function ($q) use ($field_id, $date) {
            $q->where('field_id', $field_id)
                ->whereDate('booking_date', $date)
                ->whereIn('status', ['pending', 'dp_paid', 'paid']);
        })->pluck('start_time')->map(fn($t) => \Carbon\Carbon::parse($t)->format('H:i:s'))->toArray();

        // Rangkai data untuk dikembalikan ke Mobile App
        $times = [];
        foreach ($prices as $price) {
            $dbStart = \Carbon\Carbon::parse($price->start_time)->format('H:i:s');
            $times[] = [
                'start' => \Carbon\Carbon::parse($price->start_time)->format('H:i'),
                'end' => \Carbon\Carbon::parse($price->end_time)->format('H:i'),
                'price' => $price->price,
                'is_booked' => in_array($dbStart, $bookedTimes), // Boolean: true jika penuh, false jika kosong
            ];
        }

        return response()->json([
            'success' => true,
            'date' => $date,
            'data' => $times
        ]);
    }

    // === 3. PROSES CHECKOUT PESANAN BARU ===
    public function checkout(Request $request)
    {
        $user = $request->user();

        // Validasi data yang dikirim dari HP
        $request->validate([
            'field_id' => 'required|exists:fields,id',
            'booking_date' => 'required|date',
            'payment_type' => 'required|in:full,dp',
            'subtotal' => 'required|numeric',
            'items' => 'required|array',
            'promo_id' => 'nullable|exists:promos,id',
        ]);

        $subtotal = $request->subtotal;
        $discountAmount = 0;

        // === PERHITUNGAN DISKON & VALIDASI PROMO ===
        if ($request->filled('promo_id')) {
            $promo = \App\Models\Promo::find($request->promo_id);
            if ($promo) {
                // 1. Cek apakah BATAS WAKTU promo sudah lewat
                if (\Carbon\Carbon::now()->startOfDay()->gt(\Carbon\Carbon::parse($promo->valid_until)->endOfDay())) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Maaf, masa berlaku kode promo ini sudah habis.'
                    ], 400);
                }

                // 2. Cek apakah CUSTOMER INI sudah pernah memakai promo ini
                $hasUsedPromo = \App\Models\Booking::where('user_id', $user->id)
                    ->where('promo_id', $promo->id)
                    ->where('status', '!=', 'cancelled')
                    ->exists();

                if ($hasUsedPromo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah pernah menggunakan kode promo ini.'
                    ], 400);
                }

                // 3. Cek apakah BATAS MAKSIMAL (max_uses) GLOBAL sudah terpenuhi
                $totalUsage = \App\Models\Booking::where('promo_id', $promo->id)
                    ->where('status', '!=', 'cancelled')
                    ->count();

                if ($totalUsage >= $promo->max_uses) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Maaf, kuota penggunaan kode promo ini sudah habis.'
                    ], 400);
                }

                // 4. Jika lolos semua validasi, hitung diskon
                if ($promo->discount_type === 'percent') {
                    $discountAmount = $subtotal * ($promo->discount_amount / 100);
                } else {
                    $discountAmount = $promo->discount_amount;
                }
            }
        }

        // Pastikan subtotal setelah diskon tidak minus
        $afterDiscount = max(0, $subtotal - $discountAmount);

        // PPN 11% dihitung dari harga setelah diskon
        $tax = $afterDiscount * 0.11;
        $grandTotal = $afterDiscount + $tax;

        // === TARIK PERSENTASE DP DARI DATABASE ===
        $field = \App\Models\Field::findOrFail($request->field_id);
        $dpPercent = (float) ($field->min_dp_percent ?? 50.00);

        // Tentukan jumlah yang harus dibayar sekarang
        $grossAmount = $request->payment_type === 'dp' ? ($grandTotal * ($dpPercent / 100)) : $grandTotal;

        // Buat ID Booking Unik
        $bookingCode = 'PLS-' . date('dmy') . '-' . strtoupper(\Illuminate\Support\Str::random(4));

        // Amankan database menggunakan Transaction system
        \DB::beginTransaction();

        try {
            // 1. Simpan Data ke Tabel Bookings
            $booking = \App\Models\Booking::create([
                'user_id' => $user->id,
                'field_id' => $request->field_id,
                'promo_id' => $request->promo_id,
                'booking_code' => $bookingCode,
                'booking_date' => $request->booking_date,
                'subtotal' => $subtotal,
                'discount' => $discountAmount,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'payment_type' => $request->payment_type,
                'status' => 'pending',
            ]);

            // 2. Simpan Detail Jam Bermain ke Tabel Items
            foreach ($request->items as $item) {
                $booking->items()->create([
                    'start_time' => $item['start_time'],
                    'end_time' => $item['end_time'],
                    'price' => $item['price']
                ]);
            }

            // === KONFIGURASI MIDTRANS ===
            \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            // Penyesuaian Jaringan (Memaksa IPv4 untuk Server Arenhost + Tambalan Bug Midtrans PHP 8)
            \Midtrans\Config::$curlOptions = [
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                CURLOPT_HTTPHEADER => [], // <--- INI OBAT PENAWARNYA! (Key 10023)
            ];

            $params = [
                'transaction_details' => [
                    'order_id' => $bookingCode,
                    'gross_amount' => round($grossAmount),
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '081111111111',
                ]
            ];

            // Dapatkan URL Pembayaran dari Midtrans
            $paymentUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;

            // Simpan link ke database
            $booking->update(['midtrans_snap_token' => $paymentUrl]);

            // Kunci data di database
            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil membuat pesanan',
                'payment_url' => $paymentUrl
            ]);
        } catch (\Exception $e) {
            // Batalkan semua query jika di tengah jalan Midtrans atau sistem error
            \DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke sistem pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    // === FUNGSI PEMBATALAN PESANAN (MOBILE) ===
    public function cancelBooking(Request $request, $id)
    {
        $user = $request->user();
        $booking = \App\Models\Booking::with('field')->where('id', $id)->where('user_id', $user->id)->first();

        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.']);
        }

        if ($booking->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Pesanan sudah dibatalkan sebelumnya.']);
        }

        // 1. Cek Aturan H-1
        $today = \Carbon\Carbon::now()->startOfDay();
        $bookingDate = \Carbon\Carbon::parse($booking->booking_date)->startOfDay();

        if ($today->diffInDays($bookingDate, false) < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Maaf, pembatalan maksimal dilakukan H-1 sebelum jadwal bermain.'
            ]);
        }

        // 2. Kalkulasi Pengembalian Dana (DP Hangus)
        $dpPercent = (float) ($booking->field->min_dp_percent ?? 50);
        $dpAmount = $booking->grand_total * ($dpPercent / 100);

        $refundAmount = 0;
        $message = "Pesanan berhasil dibatalkan.";

        if ($booking->status === 'paid' && $booking->paid_amount > $dpAmount) {
            // Jika Lunas, uang dikembalikan tapi dikurangi DP (karena DP hangus)
            $refundAmount = $booking->paid_amount - $dpAmount;
            $message .= " Dana Pelunasan Rp " . number_format($refundAmount, 0, ',', '.') . " akan dikembalikan ke rekening " . $request->refund_bank . ". (DP Hangus).";

            // Validasi Rekening WAJIB jika ada dana yg kembali
            $request->validate([
                'refund_bank' => 'required|string',
                'refund_account' => 'required|string',
                'refund_name' => 'required|string',
            ]);
        } elseif ($booking->status === 'dp_paid') {
            // Jika baru DP, uang tidak kembali
            $message .= " Sesuai kebijakan, DP yang telah dibayarkan hangus.";
        }

        // 3. Update Status & Rekening di Database
        $booking->update([
            'status' => 'cancelled',
            'refund_bank' => $request->refund_bank ?? null,
            'refund_account' => $request->refund_account ?? null,
            'refund_name' => $request->refund_name ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'refund_amount' => $refundAmount
        ]);
    }

    // === FITUR CHATBOT AI ===
    public function chatWithAi(Request $request, \App\Services\GeminiService $gemini)
    {
        // Validasi data dari HP
        $request->validate([
            'chatHistory' => 'required|array',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        try {
            // Lempar riwayat chat ke service Gemini yang sudah kamu buat
            $botReply = $gemini->getChatReply(
                $request->chatHistory,
                $request->lat,
                $request->lng
            );

            return response()->json([
                'success' => true,
                'reply' => $botReply
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi AI: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPromos()
    {
        // Ambil promo yang batas berlakunya masih hari ini atau ke depan
        $promos = \App\Models\Promo::whereDate('valid_until', '>=', \Carbon\Carbon::today())->get();

        return response()->json([
            'success' => true,
            'data' => $promos
        ]);
    }
}
