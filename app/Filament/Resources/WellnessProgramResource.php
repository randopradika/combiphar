<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WellnessProgramResource\Pages;
use App\Models\WellnessProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * "Employee Wellness Program" circles on the Karir tab (Figma 987:51). Unlike
 * the Investor hub cards this is not a fixed row set — admins may add or remove
 * programmes, and the grid on the page reflows to whatever count exists.
 *
 * The section heading and its lead paragraph live on the contact Page record
 * (Karir & Kontak → banner / teks halaman), not here.
 */
class WellnessProgramResource extends Resource
{
    protected static ?string $model = WellnessProgram::class;

    protected static ?string $slug = 'wellness-programs';

    protected static ?string $navigationGroup = 'Karir & Kontak';

    protected static ?string $navigationLabel = 'Program Kesejahteraan Karyawan';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $recordTitleAttribute = 'title_id';

    /**
     * Fields the panel-wide search box looks at. Both language columns are
     * listed so a search works whichever language the editor thinks in.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title_id', 'title_en'];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title_id')
                ->label('Judul (ID)')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('title_en')
                ->label('Title (EN)')
                ->maxLength(255),
            Forms\Components\Textarea::make('body_id')
                ->label('Deskripsi (ID)')
                ->rows(4),
            Forms\Components\Textarea::make('body_en')
                ->label('Description (EN)')
                ->rows(4),
            Forms\Components\FileUpload::make('icon')
                ->label('Ikon')
                ->helperText('Gambar persegi dengan latar transparan (mis. PNG 512×512). Ikon tampil di dalam lingkaran ungu; lingkaran keempat berlatar gelap, jadi gunakan ikon terang di posisi tersebut.')
                ->image()
                ->imageEditor()
                ->directory('wellness')
                ->columnSpanFull(),
            // Admins never type a sort number — new programmes append (project-wide convention).
            Forms\Components\Hidden::make('sort')
                ->default(fn () => (int) (static::getModel()::max('sort') ?? 0) + 1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                Tables\Columns\ImageColumn::make('icon')->label('Ikon'),
                Tables\Columns\TextColumn::make('title_id')->label('Judul')->searchable(),
                Tables\Columns\TextColumn::make('title_en')->label('Title (EN)')->searchable(),
                Tables\Columns\TextColumn::make('body_id')->label('Deskripsi')->limit(60),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWellnessPrograms::route('/'),
        ];
    }
}
