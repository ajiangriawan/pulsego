<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoResource\Pages;
use App\Models\Promo;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PromoResource extends Resource
{
    protected static ?string $model = Promo::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Manajemen Keuangan';
    protected static ?string $modelLabel = 'Kode Promo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code')
                    ->label('Kode Promo')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('discount_type')
                    ->label('Tipe Diskon')
                    ->options([
                        'percent' => 'Persentase (%)',
                        'fixed' => 'Nominal Tetap (Rp)',
                    ])
                    ->required()
                    ->reactive(),
                TextInput::make('discount_amount')
                    ->label('Jumlah Diskon')
                    ->numeric()
                    ->required()
                    ->prefix(fn ($get) => $get('discount_type') === 'fixed' ? 'Rp' : null)
                    ->suffix(fn ($get) => $get('discount_type') === 'percent' ? '%' : null),
                DatePicker::make('valid_until')
                    ->label('Berlaku Sampai')
                    ->required(),
                TextInput::make('max_uses')
                    ->label('Batas Maksimal Penggunaan')
                    ->numeric()
                    ->default(1)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable(),
                TextColumn::make('discount_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percent' => 'info',
                        'fixed' => 'success',
                    }),
                TextColumn::make('discount_amount')->numeric(),
                TextColumn::make('valid_until')->date()->sortable(),
                TextColumn::make('max_uses')->numeric(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromos::route('/'),
        ];
    }
}