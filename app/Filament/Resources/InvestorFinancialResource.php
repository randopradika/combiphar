<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvestorFinancialResource\Pages;
use App\Models\InvestorDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvestorFinancialResource extends Resource
{
    protected static ?string $model = InvestorDocument::class;

    protected static ?string $slug = 'investor-financial';

    protected static ?string $navigationGroup = 'Investor';

    protected static ?string $navigationLabel = 'Informasi Keuangan';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('category', 'financial');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('category')->default('financial'),
            Forms\Components\TextInput::make('title_id')->label('Judul (ID)')->required()->maxLength(255),
            Forms\Components\TextInput::make('title_en')->label('Title (EN)')->maxLength(255),
            Forms\Components\TextInput::make('year')->numeric(),
            Forms\Components\FileUpload::make('file_id')->label('File (ID)')->acceptedFileTypes(['application/pdf'])->downloadable(),
            Forms\Components\FileUpload::make('file_en')->label('File (EN)')->acceptedFileTypes(['application/pdf'])->downloadable(),
            Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort')->columns([
            Tables\Columns\TextColumn::make('title_id')->label('Judul')->searchable(),
            Tables\Columns\TextColumn::make('year')->numeric()->sortable(),
            Tables\Columns\TextColumn::make('sort')->numeric()->sortable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ])->bulkActions([
            Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageInvestorFinancial::route('/')];
    }
}
