<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestBookings extends BaseWidget
{
    protected static ?string $heading = '5 Booking Teranyar';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected function getTableGrid(): array
    {
        return [
            'md' => 1, // Full width di halaman dashboard
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Menarik data booking terbaru beserta relasi nama lapangannya
                Booking::query()->with('field')->latest()->limit(5)
            )
            ->columns([
                TextColumn::make('booking_code')
                    ->label('Kode Booking')
                    ->fontFamily('mono')
                    ->searchable(),

                TextColumn::make('field.name')
                    ->label('Lapangan'),

                TextColumn::make('booking_date')
                    ->label('Tgl Main')
                    ->date('d M Y'),

                TextColumn::make('grand_total')
                    ->label('Total Tagihan')
                    ->money('IDR', locale: 'id'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'dp_paid' => 'warning',
                        'pending' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->actions([
                // Memungkinkan admin langsung mengklik tombol edit/view langsung dari baris dashboard
                Tables\Actions\Action::make('Lihat')
                    ->url(fn (Booking $record): string => route('filament.admin.resources.fields.edit', ['record' => $record->field_id]))
                    ->icon('heroicon-m-eye')
                    ->button(),
            ]);
    }
}