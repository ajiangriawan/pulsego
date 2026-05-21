<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Field; 
use App\Models\FieldPrice;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $modelLabel = 'Data Booking';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pelanggan & Lapangan')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name', fn($query) => $query->where('role', 'customer'))
                            ->searchable()
                            ->preload()
                            ->label('Customer (Kosongkan jika Walk-in/Offline)')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->unique('users', 'email'),
                                Forms\Components\TextInput::make('phone')
                                    ->label('No. Telp / WhatsApp')
                                    ->tel()
                                    ->required(),
                                Forms\Components\TextInput::make('password')
                                    ->label('Password (Otomatis)')
                                    ->password()
                                    ->default('pulsego123')
                                    ->required()
                                    ->readOnly()
                                    ->helperText('Beritahu customer password defaultnya adalah: pulsego123'),
                                Forms\Components\Hidden::make('role')
                                    ->default('customer'),
                            ]),

                        Forms\Components\Select::make('field_id')
                            ->relationship('field', 'name')
                            ->required()
                            ->label('Pilih Lapangan')
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('selected_times', []);
                                $set('subtotal', 0);
                                $set('ppn_amount', 0);
                                $set('grand_total', 0);
                                $set('dp_amount', 0);
                            }),
                    ])->columns(2),

                Forms\Components\Section::make('Jadwal & Waktu (Pilih Jam Bermain)')
                    ->schema([
                        Forms\Components\DatePicker::make('booking_date')
                            ->required()
                            ->label('Tanggal Main')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('selected_times', [])),

                        Forms\Components\CheckboxList::make('selected_times')
                            ->label('Pilih Jam Tersedia')
                            ->options(function (Forms\Get $get) {
                                $fieldId = $get('field_id');
                                $date = $get('booking_date');
                                if (!$fieldId || !$date) return [];

                                $dayOfWeek = Carbon::parse($date)->format('l');
                                $prices = FieldPrice::where('field_id', $fieldId)
                                    ->where('day_of_week', $dayOfWeek)
                                    ->orderBy('start_time')
                                    ->get();

                                $bookedTimes = \App\Models\BookingItem::whereHas('booking', function ($q) use ($fieldId, $date) {
                                    $q->where('field_id', $fieldId)
                                        ->whereDate('booking_date', $date)
                                        ->whereIn('status', ['pending', 'dp_paid', 'paid']);
                                })->pluck('start_time')->map(fn($time) => Carbon::parse($time)->format('H:i:s'))->toArray();

                                $options = [];
                                foreach ($prices as $price) {
                                    $dbStart = Carbon::parse($price->start_time)->format('H:i:s');
                                    $start = Carbon::parse($price->start_time)->format('H:i');
                                    $end = Carbon::parse($price->end_time)->format('H:i');
                                    $val = "{$dbStart}|{$price->end_time}|{$price->price}";

                                    if (in_array($dbStart, $bookedTimes)) {
                                        $options[$val] = "{$start} - {$end} (Telah Dipesan)";
                                    } else {
                                        $options[$val] = "{$start} - {$end} (Rp " . number_format($price->price, 0, ',', '.') . ")";
                                    }
                                }
                                return $options;
                            })
                            ->disableOptionWhen(function (string $value, Forms\Get $get): bool {
                                $fieldId = $get('field_id');
                                $date = $get('booking_date');
                                if (!$fieldId || !$date) return false;

                                $startTime = explode('|', $value)[0];
                                $formattedStartTime = Carbon::parse($startTime)->format('H:i:s');

                                return \App\Models\BookingItem::where('start_time', $formattedStartTime)
                                    ->whereHas('booking', function ($query) use ($fieldId, $date) {
                                        $query->where('field_id', $fieldId)
                                            ->whereDate('booking_date', $date)
                                            ->whereIn('status', ['pending', 'dp_paid', 'paid']);
                                    })
                                    ->exists();
                            })
                            ->live()
                            
                            // =================================================================
                            // PERBAIKAN UTAMA: AMBIL MIN DP % DINAMIS DARI DATABASE LAPANGAN
                            // =================================================================
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                $subtotal = 0;
                                if (is_array($state)) {
                                    foreach ($state as $val) {
                                        $parts = explode('|', $val);
                                        if (isset($parts[2])) {
                                            $subtotal += (float) $parts[2];
                                        }
                                    }
                                }

                                $ppn = $subtotal * 0.11; 
                                $grandTotal = $subtotal + $ppn;

                                // Cari data lapangan yang dipilih untuk mendapatkan min_dp_percent aslinya
                                $fieldId = $get('field_id');
                                $minDpPercent = 50.00; // nilai default jaga-jaga jika field tidak ditemukan

                                if ($fieldId) {
                                    $field = Field::find($fieldId);
                                    if ($field) {
                                        $minDpPercent = (float) $field->min_dp_percent;
                                    }
                                }

                                // Kalkulasi nominal DP berdasarkan % dari database lapangan tersebut
                                $dpAmount = $grandTotal * ($minDpPercent / 100); 

                                $set('subtotal', $subtotal);
                                $set('ppn_amount', $ppn);
                                $set('grand_total', $grandTotal);
                                $set('dp_amount', $dpAmount);
                                
                                // Update label instruksi tipe pembayaran agar Admin tahu berapa % yang berlaku
                                $set('dynamic_dp_label', "DP ({$minDpPercent}%)");
                            })
                            ->columns(3)
                            ->required(),
                    ]),

                Forms\Components\Section::make('Rincian Biaya & Pembayaran')
                    ->schema([
                        Forms\Components\Hidden::make('booking_code')
                            ->default(fn() => 'WLK-' . strtoupper(uniqid())),

                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal (Sewa Lapangan)')
                                ->numeric()
                                ->readOnly()
                                ->prefix('Rp'),
                            Forms\Components\TextInput::make('ppn_amount')
                                ->label('PPN (11%)')
                                ->numeric()
                                ->readOnly()
                                ->dehydrated(false)
                                ->prefix('Rp'),
                            Forms\Components\TextInput::make('grand_total')
                                ->label('Total Keseluruhan')
                                ->numeric()
                                ->required()
                                ->readOnly()
                                ->prefix('Rp'),
                            Forms\Components\TextInput::make('dp_amount')
                                ->label('Minimal Wajib DP (Mengikuti Aturan Lapangan)')
                                ->numeric()
                                ->readOnly()
                                ->dehydrated(false)
                                ->prefix('Rp'),
                        ])->columns(2),

                        Forms\Components\Group::make([
                            Forms\Components\Select::make('payment_type')
                                ->label('Tipe Pembayaran')
                                ->options([
                                    'dp' => 'Uang Muka (DP)',
                                    'full' => 'Lunas (100%)',
                                ])
                                ->required()
                                ->default('full'),
                            Forms\Components\Select::make('status')
                                ->label('Status Pembayaran')
                                ->options([
                                    'pending' => 'Pending',
                                    'dp_paid' => 'DP Terbayar',
                                    'paid' => 'Lunas',
                                    'cancelled' => 'Dibatalkan',
                                ])
                                ->required()
                                ->default('paid'),
                        ])->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_code')->label('Kode')->searchable(),
                TextColumn::make('user.name')->label('Customer')->default('Walk-in (Offline)'),
                TextColumn::make('field.name')->label('Lapangan'),
                TextColumn::make('booking_date')->label('Tanggal')->date()->sortable(),
                TextColumn::make('grand_total')->label('Total')->money('idr'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'dp_paid' => 'info',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'dp_paid' => 'DP Terbayar',
                        'paid' => 'Lunas',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('printReceipt')
                    ->label('Cetak Struk')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn(Booking $record): string => route('booking.receipt', $record->booking_code))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('markAsPaid')
                    ->label('Tandai Lunas')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(Booking $record) => in_array($record->status, ['pending', 'dp_paid']))
                    ->action(fn(Booking $record) => $record->update(['status' => 'paid', 'paid_amount' => $record->grand_total])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}