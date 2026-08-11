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
use Illuminate\Support\Str;

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

    /**
     * Kolom isi di kiri, keputusan penerbitan di kanan.
     *
     * Sebelumnya sebelas bidang berbaris dalam satu kolom mengikuti urutan
     * kolom database: yang pertama ditemui editor adalah `slug` -- alamat untuk
     * mesin -- dan yang terakhir `is_featured`, dua layar di bawah, di balik dua
     * editor teks kaya. Bahasa Indonesia dan Inggris juga ditumpuk berurutan,
     * sehingga memeriksa terjemahan berarti menggulir melewati satu editor
     * penuh lalu kembali lagi. Sekarang satu bahasa tampil pada satu waktu.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        Forms\Components\Section::make()
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
                    ]),

                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Forms\Components\Section::make('Penerbitan')
                            ->description('Kosongkan tanggal untuk menyimpan sebagai draf.')
                            ->schema([
                                // Status tidak disimpan sebagai kolom sendiri: ia
                                // disimpulkan dari published_at, persis seperti
                                // yang dibaca scopePublished(). Ditampilkan di
                                // sini supaya aturannya terbaca di layar tempat
                                // ia ditentukan, bukan hanya di kolom daftar.
                                Forms\Components\Placeholder::make('status')
                                    ->label('Status saat ini')
                                    ->content(fn (?Article $record) => match (true) {
                                        $record === null => 'Draf (belum disimpan)',
                                        $record->published_at === null => 'Draf — tidak tampil di situs',
                                        ! $record->isPublished() => 'Terjadwal — tayang '.$record->published_at->translatedFormat('j F Y, H:i'),
                                        default => 'Terbit',
                                    }),
                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('Tanggal terbit')
                                    ->helperText('Kosong = draf. Tanggal di masa depan = terjadwal; artikel muncul sendiri saat waktunya tiba.')
                                    ->seconds(false)
                                    ->native(false),
                                Forms\Components\Select::make('category')
                                    ->label('Kategori')
                                    ->options([
                                        'pembaruan_korporasi' => 'Investor Update',
                                        'edukasi_gaya_hidup' => 'Health Information',
                                        'informasi_produk' => 'Product Info',
                                        'lainnya' => 'Others',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->default('edukasi_gaya_hidup'),
                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Jadikan sorotan'),
                            ]),

                        Forms\Components\Section::make('Gambar sampul')
                            ->schema([
                                Forms\Components\FileUpload::make('cover_image')
                                    ->label('')
                                    ->image()
                                    ->imageEditor()
                                    ->helperText('Tampil pada kartu berita dan sebagai gambar bagikan di media sosial.'),
                            ]),

                        Forms\Components\Section::make('Alamat halaman')
                            ->description('Bagian teknis. Biasanya tidak perlu disentuh.')
                            ->collapsed()
                            ->schema([
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Alamat artikel: /berita/{slug}. Dibuat otomatis dari judul saat artikel baru. Mengubahnya pada artikel yang sudah tayang akan mematikan tautan lama.'),
                            ]),
                    ]),
            ]);
    }

    /**
     * Bidang isi untuk satu bahasa. Dipanggil dua kali, sekali per tab, supaya
     * pasangan _id/_en tidak lagi berbaris menumpuk dalam satu kolom.
     */
    private static function contentFields(string $locale): array
    {
        $isId = $locale === 'id';

        return [
            Forms\Components\TextInput::make("title_{$locale}")
                ->label($isId ? 'Judul' : 'Title')
                ->required($isId)
                ->maxLength(255)
                // Slug diisi dari judul HANYA saat membuat artikel baru:
                // menimpanya saat menyunting akan memindahkan URL artikel yang
                // sudah tayang tanpa ada yang memintanya.
                ->live(onBlur: true)
                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state, string $operation) use ($isId) {
                    if (! $isId || $operation !== 'create' || blank($state) || filled($get('slug'))) {
                        return;
                    }

                    $set('slug', Str::slug($state));
                })
                ->columnSpanFull(),
            Forms\Components\Textarea::make("excerpt_{$locale}")
                ->label($isId ? 'Ringkasan' : 'Excerpt')
                ->rows(3)
                ->helperText($isId ? 'Teks pendek pada kartu berita.' : null)
                ->columnSpanFull(),
            Forms\Components\RichEditor::make("body_{$locale}")
                ->label($isId ? 'Isi artikel' : 'Body')
                ->columnSpanFull(),
        ];
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
