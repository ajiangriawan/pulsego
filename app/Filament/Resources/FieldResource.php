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
use Filament\Forms\Components\Select; // Pastikan Select diimport
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

                        Select::make('type')
                            ->label('Tipe / Kategori Lapangan')
                            ->options(function () {
                                // Menampilkan opsi unik secara otomatis yang sudah ada di database
                                return Field::query()
                                    ->whereNotNull('type')
                                    ->where('type', '!=', '')
                                    ->distinct()
                                    ->pluck('type', 'type')
                                    ->toArray();
                            })
                            ->placeholder('Pilih tipe atau ketik baru...')
                            ->searchable()
                            ->required()

                            // 1. Tampilkan form modal input saat admin ingin membuat opsi baru
                            ->createOptionForm([
                                Forms\Components\TextInput::make('type')
                                    ->label('Tipe Lapangan Baru')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Misal: Mini Soccer, Tenis Meja'),
                            ])

                            // 2. PERBAIKAN: Beritahu Filament cara memproses dan menyimpan opsi baru tersebut
                            ->createOptionUsing(function (array $data) {
                                // Karena ini kolom teks biasa di tabel 'fields', kita cukup mengembalikan 
                                // teks yang diketik admin agar langsung terpilih di form utama
                                return $data['type'];
                            }),

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

                        SpatieMediaLibraryFileUpload::make('gallery')
                            ->multiple()
                            ->collection('gallery')
                            ->label('Foto Lapangan (Bisa lebih dari 1)')
                            ->image()
                            ->columnSpanFull(),
                    ]),
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

                // Menampilkan tipe lapangan di tabel manajemen admin
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('min_dp_percent')
                    ->label('Min. DP (%)')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Tgl Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Menambahkan filter cepat berdasarkan Tipe Lapangan di tabel admin
                Tables\Filters\SelectFilter::make('type')
                    ->label('Filter Tipe')
                    ->options(fn() => Field::query()->whereNotNull('type')->distinct()->pluck('type', 'type')->toArray()),
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
