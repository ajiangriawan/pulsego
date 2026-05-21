<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Pendapatan Bulanan';
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Ambil rekap data pendapatan per bulan untuk tahun berjalan
        $revenueData = Booking::select(
            DB::raw('MONTH(booking_date) as month'),
            DB::raw('SUM(paid_amount) as total')
        )
        ->whereIn('status', ['paid', 'dp_paid'])
        ->whereYear('booking_date', date('Y'))
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('total', 'month')
        ->toArray();

        // Menyusun data agar bulan yang kosong tetap terisi nilai 0
        $data = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        for ($i = 1; $i <= 12; $i++) {
            $data[] = $revenueData[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan (Rp)',
                    'data' => $data,
                    'borderColor' => '#3A9E3F', // Hijau PulseGo
                    'backgroundColor' => 'rgba(58, 158, 63, 0.1)',
                    'fill' => true,
                    'tension' => 0.3, // Membuat garis grafik menjadi melengkung halus
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line'; // Jenis grafik garis
    }
}