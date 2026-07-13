<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SportResource\Pages;
use App\Models\CsrProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SportResource extends Resource
{
    protected static ?string $model = CsrProgram::class;

    protected static ?string $slug = 'sports';

    protected static ?string $navigationGroup = 'Tanggung Jawab Sosial';

    protected static ?string $navigationLabel = 'Sports (Halaman Detail)';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    /** Only sports programs: the sport pages + their team blocks. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('category', 'sports');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('category')->default('sports'),

            Forms\Components\Select::make('parent_id')
                ->label('Jenis entri')
                ->helperText('Kosongkan = halaman olahraga utama (mis. Basketball). Pilih olahraga = entri ini adalah salah satu TIM di dalamnya.')
                ->options(fn () => CsrProgram::where('category', 'sports')->whereNull('parent_id')->orderBy('sort')->pluck('title_id', 'id'))
                ->placeholder('— Halaman olahraga utama —')
                ->searchable()
                ->preload()
                ->live()
                ->nullable(),

            Forms\Components\TextInput::make('title_id')
                ->label('Nama (ID)')->required()->maxLength(255),
            Forms\Components\TextInput::make('title_en')
                ->label('Name (EN)')->maxLength(255),

            // --- Main sport page only ---
            Forms\Components\Textarea::make('body_id')
                ->label('Deskripsi Banner (ID)')
                ->helperText('Teks singkat di sebelah judul pada banner halaman olahraga.')
                ->rows(2)
                ->visible(fn (Forms\Get $get) => blank($get('parent_id'))),
            Forms\Components\Textarea::make('body_en')
                ->label('Banner Description (EN)')
                ->rows(2)
                ->visible(fn (Forms\Get $get) => blank($get('parent_id'))),
            Forms\Components\TextInput::make('slug')
                ->label('Slug URL')
                ->helperText('Alamat halaman: /csr/{slug} — mis. "basketball".')
                ->maxLength(255)
                ->required(fn (Forms\Get $get) => blank($get('parent_id')))
                ->visible(fn (Forms\Get $get) => blank($get('parent_id'))),

            // --- Team block only ---
            Forms\Components\RichEditor::make('content_id')
                ->label('Deskripsi Tim (ID)')
                ->helperText('Paragraf deskripsi yang tampil di samping nama tim.')
                ->columnSpanFull()
                ->visible(fn (Forms\Get $get) => filled($get('parent_id'))),
            Forms\Components\RichEditor::make('content_en')
                ->label('Team Description (EN)')
                ->columnSpanFull()
                ->visible(fn (Forms\Get $get) => filled($get('parent_id'))),

            Forms\Components\FileUpload::make('image')
                ->label(fn (Forms\Get $get) => blank($get('parent_id')) ? 'Banner / Gambar Kartu' : 'Logo Tim')
                ->image(),

            Forms\Components\FileUpload::make('gallery')
                ->label('Galeri Foto Tim')
                ->helperText('Foto-foto yang tampil sebagai grid di bawah tim.')
                ->image()->multiple()->reorderable()->appendFiles()
                ->visible(fn (Forms\Get $get) => filled($get('parent_id'))),

            Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                Tables\Columns\TextColumn::make('title_id')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('parent.title_id')->label('Bagian dari')->placeholder('— (olahraga utama)')->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\ImageColumn::make('image'),
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
            'index' => Pages\ListSports::route('/'),
            'create' => Pages\CreateSport::route('/create'),
            'edit' => Pages\EditSport::route('/{record}/edit'),
        ];
    }
}
