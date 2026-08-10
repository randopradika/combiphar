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
                Forms\Components\Textarea::make('question_id')
                    ->label('Pertanyaan (ID)')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('question_en')
                    ->label('Question (EN)')
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('answer_id')
                    ->label('Jawaban (ID)')
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('answer_en')
                    ->label('Answer (EN)')
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                Tables\Columns\TextColumn::make('question_id')->label('Pertanyaan')->limit(70)->searchable(),
                Tables\Columns\TextColumn::make('sort')->numeric()->sortable(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
