<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Field;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    // Mengatur agar widget melakukan refresh otomatis setiap 10 detik secara real-time
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        // 1. KUALKULASI TOTAL PENDAPATAN (SUM dari paid_amount untuk status paid & dp_paid)
        $totalRevenue = Booking::whereIn('status', ['paid', 'dp_paid'])->sum('paid_amount');

        // 2. HITUNG BOOKING AKTIF (Status pending, paid, dan dp_paid)
        $activeBookings = Booking::whereIn('status', ['pending', 'paid', 'dp_paid'])->count();

        // 3. HITUNG TOTAL LAPANGAN YANG TERDAFTAR
        $totalFields = Field::count();

        return [
            // Widget Pendapatan
            Stat::make('Total Pendapatan', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Total dana masuk dari DP & Pelunasan')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'), // Menggunakan warna hijau sukses

            // Widget Booking
            Stat::make('Booking Aktif', $activeBookings . ' Transaksi')
                ->description('Pesanan yang butuh dipantau')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            // Widget Lapangan
            Stat::make('Total Lapangan', $totalFields . ' Area')
                ->description('Jumlah lapangan olahraga aktif')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('warning'),
        ];
    }
}