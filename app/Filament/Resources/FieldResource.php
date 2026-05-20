<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FieldResource\Pages;
use App\Filament\Resources\FieldResource\RelationManagers\PricesRelationManager;
use App\Models\Field;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FieldResource extends Resource
{
    protected static ?string $model = Field::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Manajemen Lapangan';
    protected static ?string $modelLabel = 'Lapangan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Dasar')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lapangan')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('min_dp_percent')
                            ->label('Minimal DP (%)')
                            ->numeric()
                            ->default(50)
                            ->required(),
                        RichEditor::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                        TagsInput::make('facilities')
                            ->label('Fasilitas')
                            ->placeholder('Ketik fasilitas lalu tekan Enter (misal: Toilet, Kantin)')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Lokasi & Media')
                    ->schema([
                        TextInput::make('address')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('latitude')
                                    ->numeric(),
                                TextInput::make('longitude')
                                    ->numeric(),
                            ]),

                        // UBAH BAGIAN INI
                        SpatieMediaLibraryFileUpload::make('gallery')
                            ->multiple()
                            ->collection('gallery')
                            ->label('Foto Lapangan (Bisa lebih dari 1)')
                            ->image()
                            ->columnSpanFull(),
                    ]),
                // ...
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Lapangan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('min_dp_percent')
                    ->label('Min. DP (%)')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Daftarkan RelationManager yang baru kita buat
            PricesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFields::route('/'),
            'create' => Pages\CreateField::route('/create'),
            'edit' => Pages\EditField::route('/{record}/edit'),
        ];
    }
}
