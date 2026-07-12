<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GovernanceTopicResource\Pages;
use App\Models\CsrProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GovernanceTopicResource extends Resource
{
    protected static ?string $model = CsrProgram::class;

    protected static ?string $slug = 'governance-topics';

    protected static ?string $navigationGroup = 'Tanggung Jawab Sosial';

    protected static ?string $navigationLabel = 'Sub-Menu Governance';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    /** Only sub-topics under the "governance" program. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('parent', fn (Builder $q) => $q->where('slug', 'governance'));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('parent_id')
                ->default(fn () => CsrProgram::where('slug', 'governance')->value('id')),
            Forms\Components\Hidden::make('category')->default('esg'),
            Forms\Components\TextInput::make('title_id')
                ->label('Judul (ID)')->required()->maxLength(255),
            Forms\Components\TextInput::make('title_en')
                ->label('Title (EN)')->maxLength(255),
            Forms\Components\TextInput::make('slug')
                ->label('Slug')->required()->maxLength(255)
                ->helperText('mis. whistleblowing-system (untuk URL /csr/{slug}).'),
            Forms\Components\RichEditor::make('content_id')
                ->label('Isi Konten (ID)')->columnSpanFull(),
            Forms\Components\RichEditor::make('content_en')
                ->label('Content (EN)')->columnSpanFull(),
            Forms\Components\TextInput::make('sort')
                ->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                Tables\Columns\TextColumn::make('title_id')->label('Judul')->searchable(),
                Tables\Columns\TextColumn::make('slug')->searchable(),
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
            'index' => Pages\ListGovernanceTopics::route('/'),
            'create' => Pages\CreateGovernanceTopic::route('/create'),
            'edit' => Pages\EditGovernanceTopic::route('/{record}/edit'),
        ];
    }
}
