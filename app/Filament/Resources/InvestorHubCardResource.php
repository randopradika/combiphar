<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvestorHubCardResource\Pages;
use App\Models\InvestorHubCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * The Investor hub sub-menu cards (Figma 577:1260). Fixed row set — the `key`
 * decides which Investor section a card opens, so there is no create/delete;
 * admins edit the title, upload a thumbnail, reorder, and toggle visibility.
 */
class InvestorHubCardResource extends Resource
{
    protected static ?string $model = InvestorHubCard::class;

    protected static ?string $slug = 'investor-hub-cards';

    protected static ?string $navigationGroup = 'Investor';

    protected static ?string $navigationLabel = 'Kartu Sub-Menu';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')
                ->label('Kunci Bagian')
                ->helperText('Menentukan bagian yang dibuka kartu ini — tidak dapat diubah.')
                ->disabled()
                ->dehydrated(false),
            Forms\Components\Toggle::make('is_visible')
                ->label('Tampilkan di halaman Investor')
                ->default(true),
            Forms\Components\TextInput::make('title_id')
                ->label('Judul (ID)')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('title_en')
                ->label('Title (EN)')
                ->maxLength(255),
            Forms\Components\FileUpload::make('image')
                ->label('Gambar Kartu')
                ->helperText('Rasio 1:1 (mis. 1000×1000). Kosongkan untuk memakai warna ungu bawaan. Judul tampil putih di atas gambar, jadi hindari gambar yang sangat terang di bagian bawah.')
                ->image()
                ->imageEditor()
                ->directory('investor-hub')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('Gambar'),
                Tables\Columns\TextColumn::make('title_id')->label('Judul')->searchable(),
                Tables\Columns\TextColumn::make('title_en')->label('Title (EN)')->searchable(),
                Tables\Columns\IconColumn::make('is_visible')->label('Tampil')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageInvestorHubCards::route('/')];
    }
}
