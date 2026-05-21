<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\BookingItem;
use App\Models\Field; // Diimport untuk mengambil min_dp_percent secara dinamis

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    // Properti pembantu sementara untuk menampung array jam yang dipilih dari form
    public array $selectedTimesData = [];

    /**
     * Memisahkan/mengeluarkan data selected_times sebelum baris booking disimpan
     * agar tidak memicu error 'column not found' di tabel bookings.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedTimesData = $data['selected_times'] ?? [];
        unset($data['selected_times']);
        return $data;
    }

    /**
     * Dieksekusi tepat setelah record utama Booking berhasil disimpan ke database.
     */
    protected function afterCreate(): void
    {
        $booking = $this->record;
        
        // 1. Simpan item jam bermain ke tabel booking_items
        foreach ($this->selectedTimesData as $timeStr) {
            $parts = explode('|', $timeStr);
            if (count($parts) === 3) {
                BookingItem::create([
                    'booking_id' => $booking->id,
                    'start_time' => $parts[0],
                    'end_time'   => $parts[1],
                    'price'      => $parts[2],
                ]);
            }
        }
        
        // 2. PENGECEKAN NOMINAL BAYAR OTOMATIS BERDASARKAN ATURAN MIN. DP LAPANGAN DI DATABASE
        if ($booking->payment_type === 'dp') {
            
            // Mengambil aturan persentase DP asli dari data lapangan terkait
            $minDpPercent = 50.00; // Nilai default jaga-jaga jika relasi kosong
            if ($booking->field_id) {
                $field = Field::find($booking->field_id);
                if ($field) {
                    $minDpPercent = (float) $field->min_dp_percent;
                }
            }

            // Hitung nominal DP yang sah berdasarkan persentase dinamis database
            $dpFix = $booking->grand_total * ($minDpPercent / 100);
            
            // Sesuaikan pengisian paid_amount berdasarkan status yang dipilih Admin saat input
            if ($booking->status === 'dp_paid') {
                $booking->update(['paid_amount' => $dpFix]);
            } elseif ($booking->status === 'paid') {
                $booking->update(['paid_amount' => $booking->grand_total]);
            } elseif ($booking->status === 'pending') {
                $booking->update(['paid_amount' => 0]);
            }
            
        } elseif ($booking->payment_type === 'full') {
            // Jika bertipe lunas (Full 100%)
            if ($booking->status === 'paid') {
                $booking->update(['paid_amount' => $booking->grand_total]);
            } elseif ($booking->status === 'pending') {
                $booking->update(['paid_amount' => 0]);
            }
        }
    }
}