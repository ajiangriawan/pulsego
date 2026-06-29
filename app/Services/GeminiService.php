<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Field;
use App\Models\BookingItem;
use Carbon\Carbon;

class GeminiService
{
    protected string $apiKey;
    protected string $endpoint;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        $this->endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $this->apiKey;
    }

    public function getChatReply(array $chatHistory, $userLat = null, $userLng = null)
    {
        // Default koordinat Palembang jika GPS user belum terkunci
        $lat = $userLat ? (float) $userLat : -2.990934; 
        $lng = $userLng ? (float) $userLng : 104.756554;

        // INSTRUKSI SISTEM DIPERKETAT
        $systemInstruction = "Kamu adalah virtual asisten pintar resmi dari PulseGo Palembang. "
            . "ATURAN KETAT: Jawab LANGSUNG pada intinya dengan hasil akhir. JANGAN PERNAH mengucapkan 'Sebentar ya, aku carikan', 'Oke, aku cek dulu', atau kalimat penunda lainnya. "
            . "Kamu WAJIB langsung memberikan data asli menggunakan fungsi (tools). "
            . "Jika user meminta lapangan terdekat, panggil `getNearbyFields` dan langsung berikan daftarnya. "
            . "Jika user meminta lapangan kosong, panggil `getAvailableFieldsByTime` dan langsung berikan daftarnya. "
            . "Jika user menanyakan harga, mencari lapangan murah, atau mencari batasan harga, panggil `getFieldPrices` dan berikan harganya. "
            . "Lokasi GPS user saat ini: Lat: $lat, Lng: $lng. "
            . "Gunakan bahasa santai/casual yang ramah dan langsung ke inti (to the point).";

        // Mendaftarkan 3 Fungsi/Skill AI
        $tools = [
            [
                'functionDeclarations' => [
                    [
                        'name' => 'getNearbyFields',
                        'description' => 'Mengambil daftar lapangan olahraga terurut dari yang paling dekat.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => (object)[]
                        ]
                    ],
                    [
                        'name' => 'getAvailableFieldsByTime',
                        'description' => 'Mencari lapangan yang kosong/tersedia pada tanggal dan jam tertentu.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'date' => ['type' => 'STRING', 'description' => 'Format Y-m-d (contoh: 2026-05-25)'],
                                'time' => ['type' => 'STRING', 'description' => 'Format H:i (contoh: 10:00)']
                            ],
                            'required' => ['date', 'time']
                        ]
                    ],
                    [
                        'name' => 'getFieldPrices',
                        'description' => 'Mencari informasi harga lapangan berdasarkan nama lapangan atau mencari rekomendasi lapangan di bawah batasan harga tertentu.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'field_name' => ['type' => 'STRING', 'description' => 'Nama lapangan (opsional, gunakan jika user bertanya harga lapangan spesifik)'],
                                'max_price' => ['type' => 'INTEGER', 'description' => 'Batas harga maksimal per jam (opsional, gunakan jika user mencari lapangan di bawah harga tertentu)']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Memisahkan pesan murni untuk payload
        $formattedMessages = [];
        foreach ($chatHistory as $msg) {
            if (isset($msg['parts'][0]['text'])) {
                $formattedMessages[] = [
                    'role' => $msg['role'] === 'model' ? 'model' : 'user',
                    'parts' => [['text' => $msg['parts'][0]['text']]]
                ];
            }
        }

        $attempts = 0; 

        while ($attempts < 3) {
            $payload = [
                'contents' => $formattedMessages,
                'systemInstruction' => ['parts' => [['text' => $systemInstruction]]],
                'tools' => $tools,
                'generationConfig' => ['temperature' => 0.2] // Fokus pada keakuratan data
            ];

            try {
                $response = Http::withHeaders(['Content-Type' => 'application/json'])
                                ->post($this->endpoint, $payload);

                if (!$response->successful()) {
                    Log::error('Gemini API Error: ' . $response->body());
                    return "Maaf ya, koneksi AI ke database sedang terputus. Coba kirim pesan lagi? 🙇‍♂️";
                }

                $responseData = $response->json();
                $parts = $responseData['candidates'][0]['content']['parts'] ?? [];

                if (empty($parts)) {
                    return "Aku tidak menerima respon dari server pusat, coba tanyakan lagi ya!";
                }

                // Jika Gemini memutuskan untuk memanggil Fungsi Database
                if (isset($parts[0]['functionCall'])) {
                    $functionCall = $parts[0]['functionCall'];
                    $functionName = $functionCall['name'];
                    
                    // ==========================================
                    // FIX: Konversi parameter murni ke Array PHP
                    // ==========================================
                    $argsArray = isset($functionCall['args']) ? (array) $functionCall['args'] : [];

                    $dbResult = '';
                    if ($functionName === 'getNearbyFields') {
                        $dbResult = $this->getNearbyFields($lat, $lng);
                    } elseif ($functionName === 'getAvailableFieldsByTime') {
                        $dbResult = $this->getAvailableFieldsByTime($argsArray['date'] ?? null, $argsArray['time'] ?? null);
                    } elseif ($functionName === 'getFieldPrices') {
                        $dbResult = $this->getFieldPrices($argsArray['field_name'] ?? null, $argsArray['max_price'] ?? null);
                    }

                    // Rekam jejak pemanggilan fungsi
                    $formattedMessages[] = [
                        'role' => 'model',
                        'parts' => [
                            [
                                'functionCall' => [
                                    'name' => $functionName,
                                    // Kembalikan ke format Object (JSON) untuk API Google
                                    'args' => empty($argsArray) ? (object)[] : $argsArray
                                ]
                            ]
                        ]
                    ];

                    // Suapkan balasan/hasil database ke AI
                    $formattedMessages[] = [
                        'role' => 'tool',
                        'parts' => [
                            [
                                'functionResponse' => [
                                    'name' => $functionName,
                                    'response' => (object)[
                                        'output' => $dbResult
                                    ]
                                ]
                            ]
                        ]
                    ];

                    $attempts++;
                    continue; // Putar ulang loop agar AI memproses teks dari hasil DB
                }

                // Jika bukan function call, berikan teks final ke User
                return $parts[0]['text'] ?? "Pencarian selesai, tapi teks gagal diformat. Coba tanya lagi ya! 😊";

            } catch (\Exception $e) {
                Log::error('Gemini Service Loop Exception: ' . $e->getMessage());
                return "Koneksi terganggu saat membaca database, yuk coba kirim pesan lagi! ⚡";
            }
        }

        return "Data yang ditemukan terlalu besar untuk diproses cepat, mohon spesifikkan pencarian Anda. 🙏";
    }

    private function getNearbyFields($lat, $lng)
    {
        try {
            if (!$lat || !$lng || abs($lat) < 0.001 || $lat == -2.990934) {
                $fields = Field::with('prices')->take(3)->get();
                if ($fields->isEmpty()) return "Tidak ada data lapangan terdaftar.";
                
                return "Berikut lapangan di Palembang:\n" . 
                    $fields->map(function($f) {
                        $hargaMulai = $f->prices->min('price') ? "Rp " . number_format($f->prices->min('price'), 0, ',', '.') : "Harga hubungi admin";
                        return "- {$f->name} ({$f->type}) di {$f->address}. (Mulai dari {$hargaMulai})";
                    })->implode("\n") . 
                    "\n\n*(Aktifkan GPS browser untuk menghitung jarak akurat!)*";
            }

            $fields = Field::with('prices')
                ->selectRaw("id, name, address, type, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])
                ->orderBy('distance', 'asc')
                ->take(3)
                ->get();

            if ($fields->isEmpty()) return "Tidak ada lapangan di sekitar lokasi kamu.";

            return $fields->map(function($f) {
                $jarakTeks = $f->distance < 1 
                    ? number_format($f->distance * 1000, 0) . " meter" 
                    : number_format($f->distance, 1) . " km";
                $hargaMulai = $f->prices->min('price') ? "Mulai Rp " . number_format($f->prices->min('price'), 0, ',', '.') : "Hubungi admin";
                
                return "- {$f->name} ({$f->type}) | Jarak: {$jarakTeks} | Harga: {$hargaMulai} | Alamat: {$f->address}";
            })->implode("\n");

        } catch (\Exception $e) {
            Log::error('Error getNearbyFields: ' . $e->getMessage());
            return "Gagal memproses data lokasi.";
        }
    }

    private function getAvailableFieldsByTime($date, $time)
    {
        if (!$date || !$time) return "Berikan tanggal dan jam secara spesifik.";

        try {
            $formattedTime = Carbon::parse($time)->format('H:i:s');
            $dayOfWeek = Carbon::parse($date)->format('l');

            $fields = Field::whereHas('prices', function($q) use ($dayOfWeek, $formattedTime) {
                $q->where('day_of_week', $dayOfWeek)
                  ->where('start_time', '<=', $formattedTime)
                  ->where('end_time', '>', $formattedTime);
            })->with(['prices' => function($q) use ($dayOfWeek) {
                $q->where('day_of_week', $dayOfWeek);
            }])->get();

            $availableFields = $fields->filter(function($field) use ($date, $formattedTime) {
                $isBooked = BookingItem::where('start_time', $formattedTime)
                    ->whereHas('booking', function($b) use ($field, $date) {
                        $b->where('field_id', $field->id)
                          ->whereDate('booking_date', $date)
                          ->whereIn('status', ['pending', 'dp_paid', 'paid']);
                    })->exists();

                return !$isBooked; 
            })->take(3);

            if ($availableFields->isEmpty()) {
                return "Maaf, semua lapangan sudah penuh di tanggal $date jam $time.";
            }

            return $availableFields->map(function($f) use ($formattedTime) {
                $hargaSesi = $f->prices->firstWhere(function($p) use ($formattedTime) {
                    return $p->start_time <= $formattedTime && $p->end_time > $formattedTime;
                });
                $hargaTeks = $hargaSesi ? "Rp " . number_format($hargaSesi->price, 0, ',', '.') : "Belum ditentukan";
                
                return "- {$f->name} ({$f->type}) | Harga/Jam: {$hargaTeks} | Lokasi: {$f->address}";
            })->implode("\n");

        } catch (\Exception $e) {
            Log::error('Error getAvailableFieldsByTime: ' . $e->getMessage());
            return "Terjadi kendala saat mengecek jadwal database.";
        }
    }

    private function getFieldPrices($fieldName = null, $maxPrice = null)
    {
        try {
            $query = Field::with('prices');

            if (!empty($fieldName)) {
                $query->where('name', 'LIKE', '%' . $fieldName . '%');
            }

            $fields = $query->get();

            if (!empty($maxPrice)) {
                $maxPrice = (float) $maxPrice;
                $fields = $fields->filter(function ($field) use ($maxPrice) {
                    $minPrice = $field->prices->min('price');
                    return $minPrice !== null && $minPrice <= $maxPrice;
                });
            }

            if ($fields->isEmpty()) {
                return "Maaf, tidak ada data lapangan yang sesuai dengan kriteria nama atau harga tersebut.";
            }

            return $fields->take(5)->map(function($f) {
                $minPrice = $f->prices->min('price');
                $maxPrice = $f->prices->max('price');
                
                if (!$minPrice) {
                    $hargaTeks = "Harga belum diatur di sistem";
                } elseif ($minPrice == $maxPrice) {
                    $hargaTeks = "Rp " . number_format($minPrice, 0, ',', '.');
                } else {
                    $hargaTeks = "Rp " . number_format($minPrice, 0, ',', '.') . " - Rp " . number_format($maxPrice, 0, ',', '.');
                }

                return "- {$f->name} ({$f->type}): Harga berkisar {$hargaTeks}/jam. Alamat: {$f->address}";
            })->implode("\n");

        } catch (\Exception $e) {
            Log::error('Error getFieldPrices: ' . $e->getMessage());
            return "Maaf, sistem sedang kesulitan mengambil data harga dari database.";
        }
    }
}