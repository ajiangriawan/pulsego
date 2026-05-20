<?php

namespace App\Filament\Resources\FieldResource\RelationManagers;

use App\Models\FieldPrice;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class PricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';
    protected static ?string $title = 'Manajemen Harga Sewa';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('day_of_week')
                    ->options([
                        'Monday' => 'Senin',
                        'Tuesday' => 'Selasa',
                        'Wednesday' => 'Rabu',
                        'Thursday' => 'Kamis',
                        'Friday' => 'Jumat',
                        'Saturday' => 'Sabtu',
                        'Sunday' => 'Minggu',
                    ])
                    ->required(),
                TimePicker::make('start_time')->seconds(false)->required(),
                TimePicker::make('end_time')->seconds(false)->required(),
                TextInput::make('price')->numeric()->prefix('Rp')->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('day_of_week')
            ->columns([
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('Hari')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                        'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
                        default => $state,
                    })->sortable(),
                Tables\Columns\TextColumn::make('start_time')->time('H:i')->label('Mulai'),
                Tables\Columns\TextColumn::make('end_time')->time('H:i')->label('Selesai'),
                Tables\Columns\TextColumn::make('price')->money('idr')->label('Harga'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Tambah Manual'),
                
                // Fitur Bulk Input yang diminta
                Action::make('bulk_create_prices')
                    ->label('Bulk Set Harga')
                    ->icon('heroicon-o-bolt')
                    ->color('success')
                    ->form([
                        CheckboxList::make('days')
                            ->label('Pilih Hari (Bisa lebih dari 1)')
                            ->options([
                                'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                                'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
                            ])
                            ->required()
                            ->columns(3),
                        TimePicker::make('start_time')->label('Jam Mulai')->seconds(false)->required(),
                        TimePicker::make('end_time')->label('Jam Selesai')->seconds(false)->required(),
                        TextInput::make('price')->label('Harga')->numeric()->prefix('Rp')->required(),
                    ])
                    ->action(function (array $data, $livewire) {
                        $fieldId = $livewire->ownerRecord->id;
                        foreach ($data['days'] as $day) {
                            FieldPrice::create([
                                'field_id' => $fieldId,
                                'day_of_week' => $day,
                                'start_time' => $data['start_time'],
                                'end_time' => $data['end_time'],
                                'price' => $data['price'],
                            ]);
                        }
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}