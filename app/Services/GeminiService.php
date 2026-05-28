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
        $lat = $userLat ? (float) $userLat : -2.990934; 
        $lng = $userLng ? (float) $userLng : 104.756554;

        // INSTRUKSI SISTEM DIPERKETAT AGAR TIDAK BERTELE-TELE
        $systemInstruction = "Kamu adalah virtual asisten pintar resmi dari PulseGo Palembang. "
            . "ATURAN KETAT: Jawab LANGSUNG pada intinya dengan hasil akhir. JANGAN PERNAH mengucapkan 'Sebentar ya, aku carikan', 'Oke, aku cek dulu', atau kalimat penunda lainnya. "
            . "Kamu WAJIB langsung memberikan data asli menggunakan fungsi (tools). "
            . "Jika user meminta lapangan terdekat, panggil `getNearbyFields` dan langsung berikan daftarnya. "
            . "Jika user meminta lapangan kosong, panggil `getAvailableFieldsByTime` dan langsung berikan daftarnya. "
            . "Lokasi GPS user saat ini: Lat: $lat, Lng: $lng. "
            . "Gunakan bahasa santai/casual yang ramah dan langsung ke inti (to the point).";

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
                'generationConfig' => ['temperature' => 0.2] // Turunkan temperature agar fokus pada instruksi
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

                // Jika Gemini memanggil Function
                if (isset($parts[0]['functionCall'])) {
                    $functionCall = $parts[0]['functionCall'];
                    $functionName = $functionCall['name'];
                    
                    $arguments = !empty($functionCall['args']) ? $functionCall['args'] : (object)[];

                    $dbResult = '';
                    if ($functionName === 'getNearbyFields') {
                        $dbResult = $this->getNearbyFields($lat, $lng);
                    } elseif ($functionName === 'getAvailableFieldsByTime') {
                        $dbResult = $this->getAvailableFieldsByTime($arguments['date'] ?? null, $arguments['time'] ?? null);
                    }

                    // Rekam instruksi pemanggilan dari AI
                    $formattedMessages[] = [
                        'role' => 'model',
                        'parts' => [
                            [
                                'functionCall' => [
                                    'name' => $functionName,
                                    'args' => $arguments
                                ]
                            ]
                        ]
                    ];

                    // Suapkan hasil database ke AI
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
                    continue; 
                }

                // Berikan teks final
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
                $fields = Field::take(3)->get();
                if ($fields->isEmpty()) return "Tidak ada data lapangan terdaftar.";
                
                return "Berikut lapangan di Palembang:\n" . 
                    $fields->map(fn($f) => "- {$f->name} ({$f->type}) di {$f->address}")->implode("\n") . 
                    "\n\n*(Aktifkan GPS browser untuk menghitung jarak akurat!)*";
            }

            $fields = Field::selectRaw("name, address, type, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])
                ->orderBy('distance', 'asc')
                ->take(3)
                ->get();

            if ($fields->isEmpty()) return "Tidak ada lapangan di sekitar lokasi kamu.";

            return $fields->map(function($f) {
                $jarakTeks = $f->distance < 1 
                    ? number_format($f->distance * 1000, 0) . " meter" 
                    : number_format($f->distance, 1) . " km";
                return "- {$f->name} ({$f->type}) | Jarak: {$jarakTeks} | Alamat: {$f->address}";
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

            // 1. Ambil lapangan yang buka pada hari dan jam tersebut
            $fields = Field::whereHas('prices', function($q) use ($dayOfWeek, $formattedTime) {
                $q->where('day_of_week', $dayOfWeek)
                  ->where('start_time', '<=', $formattedTime)
                  ->where('end_time', '>', $formattedTime);
            })->get();

            // 2. PERBAIKAN: Cek manual menggunakan tabel BookingItem langsung agar aman dari relasi model yang hilang
            $availableFields = $fields->filter(function($field) use ($date, $formattedTime) {
                $isBooked = BookingItem::where('start_time', $formattedTime)
                    ->whereHas('booking', function($b) use ($field, $date) {
                        $b->where('field_id', $field->id)
                          ->whereDate('booking_date', $date)
                          ->whereIn('status', ['pending', 'dp_paid', 'paid']);
                    })->exists();

                return !$isBooked; // Loloskan jika BELUM di-booking
            })->take(2);

            if ($availableFields->isEmpty()) {
                return "Maaf, semua lapangan sudah penuh di tanggal $date jam $time.";
            }

            return $availableFields->map(fn($f) => "- {$f->name} ({$f->type}), Lokasi: {$f->address}")->implode("\n");

        } catch (\Exception $e) {
            Log::error('Error getAvailableFieldsByTime: ' . $e->getMessage());
            return "Terjadi kendala saat mengecek jadwal database.";
        }
    }
}