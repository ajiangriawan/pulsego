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
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;

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

                                $fieldId = $get('field_id');
                                $minDpPercent = 50.00;

                                if ($fieldId) {
                                    $field = Field::find($fieldId);
                                    if ($field) {
                                        $minDpPercent = (float) $field->min_dp_percent;
                                    }
                                }

                                $dpAmount = $grandTotal * ($minDpPercent / 100);

                                $set('subtotal', $subtotal);
                                $set('ppn_amount', $ppn);
                                $set('grand_total', $grandTotal);
                                $set('dp_amount', $dpAmount);
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

                Section::make('Informasi Pengembalian Dana (Refund)')
                    ->description('Detail rekening pelanggan untuk pencairan dana pelunasan. (Hanya muncul untuk transaksi batal yang memiliki sisa dana).')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('refund_bank')
                                ->label('Bank Tujuan')
                                ->disabled(), 
                            TextInput::make('refund_account')
                                ->label('Nomor Rekening')
                                ->disabled(), 
                            TextInput::make('refund_name')
                                ->label('Atas Nama')
                                ->disabled(), 
                        ]),
                    ])
                    ->visible(function (?Booking $record): bool {
                        // Form Rekening HANYA muncul jika mode Edit, status dibatalkan, dan data refund tidak kosong
                        if (!$record) {
                            return false;
                        }
                        return $record->status === 'cancelled' && !empty($record->refund_bank);
                    }),
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
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu Pembayaran',
                        'dp_paid' => 'DP Terbayar',
                        'paid' => 'Lunas',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($state),
                    }),
                IconColumn::make('is_refunded')
                    ->label('Status Refund')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        // Render icon hanya untuk transaksi batal dan memiliki data rekening
                        if ($record && $record->status === 'cancelled' && $record->refund_bank !== null) {
                            return (bool) $record->is_refunded;
                        }
                        return null; // Kosong jika belum batal / tidak perlu refund
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
                TernaryFilter::make('is_refunded')
                    ->label('Filter Status Refund')
                    ->placeholder('Semua Transaksi')
                    ->trueLabel('Sudah Di-refund')
                    ->falseLabel('Belum Di-refund')
                    ->queries(
                        true: fn($query) => $query->where('status', 'cancelled')->where('is_refunded', true),
                        false: fn($query) => $query->where('status', 'cancelled')->whereNotNull('refund_bank')->where('is_refunded', false),
                        blank: fn($query) => $query,
                    ),
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
                
                Tables\Actions\Action::make('cancelBooking')
                    ->label('Batalkan Pesanan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Pesanan Ini?')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan pesanan ini dari sisi Admin? (Status akan berubah menjadi Dibatalkan).')
                    ->visible(fn(Booking $record) => in_array($record->status, ['pending', 'dp_paid', 'paid']))
                    ->action(fn(Booking $record) => $record->update(['status' => 'cancelled'])),

                Action::make('markAsRefunded')
                    ->label('Tandai Sudah Transfer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation() 
                    ->modalHeading('Konfirmasi Transfer Pengembalian Dana')
                    ->modalDescription('Pastikan Anda sudah mentransfer dana ke rekening pelanggan sebelum menandai ini. Tindakan ini akan mengubah status refund menjadi Selesai.')
                    ->visible(fn($record) => $record->status === 'cancelled' && $record->refund_bank !== null && $record->is_refunded == false)
                    ->action(function ($record) {
                        $record->update(['is_refunded' => true]);
                    }),
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