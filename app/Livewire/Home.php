<?php

namespace App\Livewire;

use App\Models\Field;
use Livewire\Component;
use Livewire\Attributes\On;

class Home extends Component
{
    public $search = '';
    public $userLat = null;
    public $userLng = null;
    public $locationError = null;

    // Listener untuk menangkap sinyal lokasi dari browser (Frontend)
    #[On('setUserLocation')]
    public function setUserLocation($lat, $lng)
    {
        $this->userLat = (float) $lat;
        $this->userLng = (float) $lng;
    }

    #[On('setLocationError')]
    public function setLocationError($errorMessage)
    {
        $this->locationError = $errorMessage;
    }

    public function render()
    {
        // Hubungkan eager loading ke relasi harga lapangan
        $query = Field::with('prices');

        // Fitur Pencarian Nama/Alamat Lapangan jika ada input
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('address', 'like', '%' . $this->search . '%');
            });
        }

        // KUNCI UTAMA: Jika koordinat GPS customer tersedia, hitung jarak terdekat
        if ($this->userLat && $this->userLng) {
            /**
             * Formula Haversine (6371 adalah radius bumi dalam kilometer)
             * Menghitung jarak lurus matematika antara 2 titik koordinat
             */
            $query->selectRaw("*, (
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) AS distance", [$this->userLat, $this->userLng, $this->userLat])
            ->orderBy('distance', 'asc'); // Urutkan dari yang jaraknya paling kecil (terdekat)
        } else {
            // Fallback: Jika GPS mati/ditolak, urutkan berdasarkan data terbaru
            $query->latest();
        }

        // Ambil data lapangan (misal dibatasi 2 untuk ringkasan beranda, atau gunakan ->get())
        $fields = $query->take(2)->get();

        return view('livewire.home', [
            'fields' => $fields
        ])->layout('layouts.app');
    }
}