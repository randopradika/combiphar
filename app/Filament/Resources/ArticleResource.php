<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\URL;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Artikel';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $recordTitleAttribute = 'title_id';

    /**
     * Fields the panel-wide search box looks at. Both language columns are
     * listed so a search works whichever language the editor thinks in.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title_id', 'title_en'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title_en')
                    ->maxLength(255),
                Forms\Components\Textarea::make('excerpt_id')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('excerpt_en')
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('body_id')
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('body_en')
                    ->columnSpanFull(),
                Forms\Components\Select::make('category')
                    ->options([
                        'pembaruan_korporasi' => 'Investor Update',
                        'edukasi_gaya_hidup' => 'Health Information',
                        'informasi_produk' => 'Product Info',
                        'lainnya' => 'Others',
                    ])
                    ->required()
                    ->default('edukasi_gaya_hidup'),
                Forms\Components\FileUpload::make('cover_image')
                    ->image(),
                Forms\Components\DateTimePicker::make('published_at'),
                Forms\Components\Toggle::make('is_featured')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title_en')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('cover_image'),
                // published_at sudah lama menentukan tayang atau tidak, tetapi
                // tabelnya hanya menampilkan tanggal mentah -- kosong berarti
                // draf, dan tanggal di masa depan berarti terjadwal, dua hal
                // yang tidak terbaca dari kolom tanggal saja.
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Article $record) => match (true) {
                        $record->published_at === null => 'Draf',
                        ! $record->isPublished() => 'Terjadwal',
                        default => 'Terbit',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'Draf' => 'gray',
                        'Terjadwal' => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('draf')
                    ->label('Belum tayang (draf & terjadwal)')
                    ->query(fn (Builder $query) => $query->where(
                        fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '>', now()),
                    )),
            ])
            ->emptyStateHeading('Belum ada artikel')
            ->emptyStateDescription('Artikel tampil di halaman Berita dan tiga terbaru muncul di Beranda. Kosongkan tanggal terbit untuk menyimpannya sebagai draf.')
            ->emptyStateIcon('heroicon-o-newspaper')
            ->actions([
                Tables\Actions\ViewAction::make()->slideOver(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('pratinjau')
                    ->label('Pratinjau')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    // Tautan bertanda tangan 24 jam, sehingga artikel draf bisa
                    // ditunjukkan ke orang tanpa akun CMS sebelum diterbitkan.
                    ->url(fn (Article $record) => static::previewUrl($record))
                    ->openUrlInNewTab()
                    ->visible(fn (Article $record) => filled($record->slug)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Tautan pratinjau bertanda tangan ke halaman detail berita.
     *
     * Nama route bersufiks locale (news.show.id / news.show.en) sesuai
     * CLAUDE.md §9 -- route('news.show') sudah tidak ada.
     */
    public static function previewUrl(Article $record): string
    {
        return URL::temporarySignedRoute(
            'news.show.'.config('app.locale'),
            now()->addDay(),
            ['slug' => $record->slug],
        );
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
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
