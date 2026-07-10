<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvestorDocumentResource\Pages;
use App\Filament\Resources\InvestorDocumentResource\RelationManagers;
use App\Models\InvestorDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvestorDocumentResource extends Resource
{
    protected static ?string $model = InvestorDocument::class;

    protected static ?string $navigationGroup = 'Investor';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category')
                    ->options([
                        'annual_report' => 'Annual Report',
                        'sustainability' => 'Sustainability Report',
                        'financial' => 'Financial Information',
                        'disclosure' => 'Information Disclosure',
                        'stock' => 'Stock Information',
                        'presentation' => 'Company Presentation',
                    ])
                    ->required()
                    ->default('annual_report'),
                Forms\Components\TextInput::make('title_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title_en')
                    ->maxLength(255),
                Forms\Components\TextInput::make('year')
                    ->numeric(),
                Forms\Components\FileUpload::make('file_id')->label('File (ID)')->acceptedFileTypes(['application/pdf'])->downloadable(),
                Forms\Components\FileUpload::make('file_en')->label('File (EN)')->acceptedFileTypes(['application/pdf'])->downloadable(),
                Forms\Components\TextInput::make('sort')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title_en')
                    ->searchable(),
                Tables\Columns\TextColumn::make('year')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('file_en')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sort')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvestorDocuments::route('/'),
            'create' => Pages\CreateInvestorDocument::route('/create'),
            'edit' => Pages\EditInvestorDocument::route('/{record}/edit'),
        ];
    }
}
