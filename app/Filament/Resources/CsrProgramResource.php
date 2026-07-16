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
                Forms\Components\Select::make('layout')
                    ->label('Tata Letak Halaman Detail')
                    ->helperText('Pilih tampilan halaman detail /csr/{slug}. "Slider Program" memakai banner + intro + slider sub-program (mis. Social Care); "Galeri Foto" memakai grid foto (mis. Environmental); "Artikel" memakai konten + formulir kontak.')
                    ->options([
                        'default' => 'Artikel + Formulir Kontak (default)',
                        'gallery' => 'Galeri Foto (mis. Environmental)',
                        'slider' => 'Slider Program (mis. Social Care)',
                    ])
                    ->default('default')
                    ->native(false),
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
                    ->helperText('Teks singkat yang tampil di kartu CSR. Juga dipakai sebagai subjudul di banner halaman detail (mis. Environmental).')
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
                Forms\Components\Repeater::make('gallery')
                    ->label('Galeri Foto (halaman detail)')
                    ->helperText('Foto-foto grid di halaman detail /csr/{slug} (mis. Environmental). Setiap foto punya keterangan (caption) sendiri. Jika diisi, halaman memakai tata letak galeri (banner + grid foto). Kosongkan untuk tata letak artikel biasa.')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('caption_id')
                            ->label('Keterangan (ID)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('caption_en')
                            ->label('Caption (EN)')
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['caption_id'] ?? null)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('link')
                    ->label('Link "Pelajari Lebih Lanjut" / "See All" (opsional)')
                    ->helperText('Tujuan tombol "Pelajari Lebih Lanjut" di kartu, dan link "See All / Lihat Semua" di bawah galeri halaman detail.')
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
