<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LegalPageResource\Pages;
use App\Models\LegalPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LegalPageResource extends Resource
{
    protected static ?string $model = LegalPage::class;

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Halaman Legal';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'title_id';

    /**
     * Fields the panel-wide search box looks at. Both language columns are
     * listed so a search works whichever language the editor thinks in.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title_id', 'title_en'];
    }

    /** Toolbar for the legal bodies — headings included; `.legal-body` styles h2/h3. */
    private const TOOLBAR = [
        'bold', 'italic', 'underline', 'strike',
        'h2', 'h3',
        'bulletList', 'orderedList',
        'link', 'undo', 'redo',
    ];

    /**
     * Formulir ini tampil di dalam modal (halaman ini memakai ManageRecords).
     * Dua editor teks kaya yang berjajar menumpuk berarti versi Inggris berada
     * beberapa layar di bawah versi Indonesianya; tab bahasa menghapus jarak itu.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Isi halaman')
                    ->schema([
                        Forms\Components\Tabs::make('Bahasa')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('Bahasa Indonesia')
                                    ->schema(static::contentFields('id')),
                                Forms\Components\Tabs\Tab::make('English')
                                    ->schema(static::contentFields('en')),
                            ])
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Lanjutan')
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->label('Kunci')
                            ->helperText('Identitas halaman (terms / privacy) — jangan diubah.')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }

    /** Judul dan isi halaman legal untuk satu bahasa. */
    private static function contentFields(string $locale): array
    {
        $isId = $locale === 'id';

        return [
            Forms\Components\TextInput::make("title_{$locale}")
                ->label($isId ? 'Judul' : 'Title')
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\RichEditor::make("body_{$locale}")
                ->label($isId ? 'Isi Halaman' : 'Page content')
                ->helperText($isId ? 'Gunakan Judul 2 / Judul 3 untuk sub-bagian dan tebal untuk penekanan.' : null)
                ->toolbarButtons(self::TOOLBAR)
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->searchable(),
                Tables\Columns\TextColumn::make('title_id')->label('Judul')->searchable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->emptyStateHeading('Belum ada halaman legal')
            ->emptyStateDescription('Syarat & Ketentuan dan Kebijakan Privasi yang ditautkan dari footer.')
            ->emptyStateIcon('heroicon-o-scale')
            ->actions([
                Tables\Actions\ViewAction::make()->slideOver(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLegalPages::route('/'),
        ];
    }
}
