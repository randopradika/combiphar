<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryPageResource\Pages;
use App\Models\CsrProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Events beneath the CSR pages that use the Basketball ("Galeri Tim") layout —
 * Environmental and Education today. An event is the CSR equivalent of a team
 * block on a sport page: its own title, description and photo gallery, rendered
 * through SportsDetail by csrShow().
 *
 * ⚠️ EVENTS ONLY. The main pages themselves are deliberately NOT listed here —
 * they are ESG / health-campaign cards on the CSR page, and everything about
 * them (banner, card text, slug, link, and the layout that opts them into this
 * menu) is edited in "Program CSR". Listing them in both places invited edits
 * from a form that only understood half of what the row is.
 */
class GalleryPageResource extends Resource
{
    protected static ?string $model = CsrProgram::class;

    protected static ?string $slug = 'gallery-pages';

    protected static ?string $navigationGroup = 'Tanggung Jawab Sosial';

    protected static ?string $navigationLabel = 'Event Gallery CSR';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    /** Only child rows whose parent page uses the Galeri Tim layout. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('category', '!=', 'sports')
            ->whereNotNull('parent_id')
            ->whereHas('parent', fn (Builder $p) => $p->where('layout', 'sports'));
    }

    /** Pages that can hold events — the "Galeri Tim" ones, minus sports. */
    private static function parentOptions(): array
    {
        return CsrProgram::query()
            ->where('layout', 'sports')
            ->where('category', '!=', 'sports')
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->pluck('title_id', 'id')
            ->all();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('parent_id')
                ->label('Bagian dari Halaman')
                ->helperText('Halaman tempat event ini tampil. Daftar ini berisi halaman CSR yang memakai tata letak "Galeri Tim" — atur tata letaknya di menu "Program CSR".')
                ->options(fn () => static::parentOptions())
                ->required()
                ->searchable()
                ->preload(),

            Forms\Components\TextInput::make('title_id')
                ->label('Nama Event (ID)')->required()->maxLength(255),
            Forms\Components\TextInput::make('title_en')
                ->label('Event Name (EN)')->maxLength(255),

            Forms\Components\RichEditor::make('content_id')
                ->label('Deskripsi Event (ID)')
                ->helperText('Paragraf yang tampil di samping nama event.')
                ->columnSpanFull(),
            Forms\Components\RichEditor::make('content_en')
                ->label('Event Description (EN)')
                ->columnSpanFull(),

            Forms\Components\FileUpload::make('image')
                ->label('Logo / Gambar Event')
                ->image(),

            Forms\Components\FileUpload::make('gallery')
                ->label('Galeri Foto Event')
                ->helperText('Grid foto di bawah event, 6 foto per halaman. Seret untuk mengubah urutan — urutan inilah yang menentukan isi Halaman 1, 2, dan seterusnya.')
                ->image()->multiple()->reorderable()->appendFiles()
                // Older CSR galleries store {image, caption_id, caption_en};
                // this layout shows no captions and saves plain paths, so
                // flatten on load or the field would hydrate empty.
                ->formatStateUsing(fn ($state) => collect($state ?? [])
                    ->map(fn ($g) => is_array($g) ? ($g['image'] ?? null) : $g)
                    ->filter()
                    ->values()
                    ->all()),

            Forms\Components\Hidden::make('sort')
                ->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                Tables\Columns\TextColumn::make('title_id')->label('Nama Event')->searchable(),
                Tables\Columns\TextColumn::make('parent.title_id')
                    ->label('Bagian dari')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image')->label('Gambar'),
                Tables\Columns\TextColumn::make('gallery')
                    ->label('Jumlah Foto')
                    ->state(fn (CsrProgram $record) => count($record->gallery ?? [])),
            ])
            ->emptyStateHeading('Belum ada event')
            ->emptyStateDescription('Tambah event untuk halaman Environmental atau Education. Halaman utamanya sendiri diatur di menu "Program CSR".')
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGalleryPages::route('/'),
            'create' => Pages\CreateGalleryPage::route('/create'),
            'edit' => Pages\EditGalleryPage::route('/{record}/edit'),
        ];
    }
}
