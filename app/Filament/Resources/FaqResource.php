<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static ?string $navigationGroup = 'Formulir & Pesan';

    protected static ?string $navigationLabel = 'Pertanyaan Umum';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $recordTitleAttribute = 'question_id';

    /**
     * Fields the panel-wide search box looks at. Both language columns are
     * listed so a search works whichever language the editor thinks in.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['question_id', 'question_en'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tanya jawab')
                    ->description('Tampil sebagai akordeon di tab Kontak pada halaman Karir & Kontak.')
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

                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
            ]);
    }

    /** Satu pasang pertanyaan dan jawaban untuk satu bahasa. */
    private static function contentFields(string $locale): array
    {
        $isId = $locale === 'id';

        return [
            Forms\Components\Textarea::make("question_{$locale}")
                ->label($isId ? 'Pertanyaan' : 'Question')
                ->required($isId)
                ->rows(2)
                ->columnSpanFull(),
            Forms\Components\Textarea::make("answer_{$locale}")
                ->label($isId ? 'Jawaban' : 'Answer')
                ->rows(6)
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                Tables\Columns\TextColumn::make('question_id')->label('Pertanyaan')->limit(70)->searchable(),
                Tables\Columns\TextColumn::make('sort')->numeric()->sortable(),
            ])
            ->emptyStateHeading('Belum ada pertanyaan umum')
            ->emptyStateDescription('Daftar tanya jawab di tab Kontak pada halaman Karir & Kontak.')
            ->emptyStateIcon('heroicon-o-question-mark-circle')
            ->actions([
                Tables\Actions\ViewAction::make()->slideOver(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
