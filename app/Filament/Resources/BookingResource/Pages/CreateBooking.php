<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\BookingItem;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    public array $selectedTimesData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedTimesData = $data['selected_times'] ?? [];
        unset($data['selected_times']);
        return $data;
    }

    protected function afterCreate(): void
    {
        $booking = $this->record;
        
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
        
        // Pengecekan otomatis nominal bayar (30% atau 100%)
        if ($booking->payment_type === 'dp') {
            // Set DP Fix = 30% dari Grand Total
            $dpFix = $booking->grand_total * 0.30;
            
            // Jika admin langsung memilih status DP Terbayar, isi nominal paid_amount
            if (in_array($booking->status, ['dp_paid', 'pending'])) {
                $booking->update(['paid_amount' => $dpFix]);
            } elseif ($booking->status === 'paid') {
                $booking->update(['paid_amount' => $booking->grand_total]);
            }
        } elseif ($booking->payment_type === 'full') {
            // Lunas 100%
            $booking->update(['paid_amount' => $booking->grand_total]);
        }
    }
}