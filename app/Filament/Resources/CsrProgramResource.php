<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CsrProgramResource\Pages;
use App\Filament\Resources\CsrProgramResource\RelationManagers;
use App\Models\CsrProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CsrProgramResource extends Resource
{
    protected static ?string $model = CsrProgram::class;

    protected static ?string $navigationGroup = 'Tanggung Jawab Sosial';

    protected static ?string $navigationLabel = 'Program CSR';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /** ESG + Health Campaign only. Sports are managed in SportResource. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('category', '!=', 'sports');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category')
                    ->options([
                        'esg' => 'ESG (Environmental, Social, Governance)',
                        'health_campaign' => 'Health Campaign',
                    ])
                    ->required()
                    ->default('esg'),
                Forms\Components\TextInput::make('title_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title_en')
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug (halaman detail, opsional)')
                    ->helperText('Isi untuk membuat halaman detail di /csr/{slug}; tombol "Pelajari Lebih Lanjut" akan mengarah ke sana.')
                    ->maxLength(255),
                Forms\Components\Select::make('parent_id')
                    ->label('Induk (jadikan sub-topik dari program lain)')
                    ->helperText('Kosongkan untuk kartu utama; pilih induk (mis. Governance) untuk menjadi sub-topik di halaman detailnya.')
                    ->relationship('parent', 'title_id', fn (Builder $query) => $query->whereNull('parent_id'))
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\Textarea::make('body_id')
                    ->label('Deskripsi Kartu / Excerpt (ID)')
                    ->helperText('Teks singkat yang tampil di kartu CSR.')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('body_en')
                    ->label('Card Excerpt (EN)')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('content_id')
                    ->label('Isi Halaman Detail (ID)')
                    ->helperText('Konten lengkap untuk halaman detail /csr/{slug}.')
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('content_en')
                    ->label('Detail Page Content (EN)')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->image(),
                Forms\Components\TextInput::make('link')
                    ->label('Link "Pelajari Lebih Lanjut" (opsional)')
                    ->url()
                    ->maxLength(255),
                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
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
                Tables\Columns\ImageColumn::make('image'),
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
            'index' => Pages\ListCsrPrograms::route('/'),
            'create' => Pages\CreateCsrProgram::route('/create'),
            'edit' => Pages\EditCsrProgram::route('/{record}/edit'),
        ];
    }
}
