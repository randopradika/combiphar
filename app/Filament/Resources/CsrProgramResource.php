<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CsrProgramResource\Pages;
use App\Models\CsrProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\URL;

/**
 * Semua baris csr_programs dalam satu daftar.
 *
 * Sebelumnya tabel ini dipecah menjadi empat menu — "Program CSR",
 * "Dokumen Governance", "Olahraga" dan "Galeri Acara" — yang masing-masing
 * hanya menyaring kolom yang berbeda. Akibatnya pertanyaan sesederhana "di mana
 * saya menambah foto Basketball?" punya tiga jawaban yang masuk akal dan hanya
 * satu yang benar, dan sebuah baris tidak dapat dipindahkan antar jenis.
 *
 * Sekarang: satu daftar, satu formulir, dengan tab dan bidang yang menyesuaikan
 * JENIS ENTRI. Jenis tidak disimpan sebagai kolom — ia disimpulkan dari
 * `category` dan `parent_id`, persis seperti keempat menu lama menyaringnya.
 */
class CsrProgramResource extends Resource
{
    protected static ?string $model = CsrProgram::class;

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Program & Halaman CSR';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $recordTitleAttribute = 'title_id';

    protected static ?string $modelLabel = 'Entri CSR';

    protected static ?string $pluralModelLabel = 'Program & Halaman CSR';

    /**
     * Fields the panel-wide search box looks at. Both language columns are
     * listed so a search works whichever language the editor thinks in.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title_id', 'title_en'];
    }

    /**
     * Halaman induk yang memakai tata letak "Galeri Tim" (mis. Environmental,
     * Education). Anak dari halaman ini adalah EVENT, bukan sub-topik biasa.
     *
     * Dicache per request: dipanggil dari closure visible() yang dievaluasi
     * berkali-kali saat formulir dirender.
     */
    private static function sportsLayoutParentIds(): array
    {
        static $ids = null;

        return $ids ??= CsrProgram::query()
            ->where('layout', 'sports')
            ->whereNull('parent_id')
            ->pluck('id')
            ->all();
    }

    /**
     * ⚠️ Kolom `gallery` menyimpan DUA bentuk berbeda:
     *   - blok tim / event  → daftar path foto biasa (tanpa keterangan)
     *   - program CSR lain  → objek {image, caption_id, caption_en}
     *
     * Pembedanya BUKAN `layout`: Environmental dan Education memakai tata letak
     * "sports" tetapi galeri halaman utamanya tetap berketerangan. Yang
     * menentukan adalah apakah baris ini sebuah BLOK (tim atau event), yaitu
     * kategori sports atau induknya memakai tata letak Galeri Tim. Salah
     * memilih komponen akan menghapus keterangan foto saat disimpan.
     */
    public static function usesPlainGallery(?string $category, $parentId): bool
    {
        if ($category === 'sports') {
            return true;
        }

        return filled($parentId) && in_array((int) $parentId, static::sportsLayoutParentIds(), true);
    }

    /** Jenis entri, disimpulkan sama seperti keempat menu lama menyaringnya. */
    public static function entryType(CsrProgram $record): string
    {
        if ($record->category === 'sports') {
            return blank($record->parent_id) ? 'Halaman olahraga' : 'Tim';
        }

        if (blank($record->parent_id)) {
            return 'Program utama';
        }

        if (in_array((int) $record->parent_id, static::sportsLayoutParentIds(), true)) {
            return 'Event';
        }

        return $record->parent?->slug === 'governance' ? 'Dokumen governance' : 'Sub-topik';
    }

    public static function form(Form $form): Form
    {
        $isBlock = fn (Forms\Get $get) => static::usesPlainGallery($get('category'), $get('parent_id'));
        $isTopLevel = fn (Forms\Get $get) => blank($get('parent_id'));

        return $form
            ->schema([
                Forms\Components\Section::make('Jenis entri')
                    ->description('Dua pilihan ini menentukan bidang apa saja yang muncul di bawah.')
                    ->schema([
                        Forms\Components\Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'esg' => 'ESG (Environmental, Social, Governance)',
                                'health_campaign' => 'Health Campaign',
                                'sports' => 'Olahraga',
                            ])
                            ->required()
                            ->default('esg')
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('parent_id', null)),
                        Forms\Components\Select::make('parent_id')
                            ->label('Bagian dari')
                            ->helperText('Kosongkan untuk halaman/kartu utama. Pilih induk untuk menjadi sub-topik, tim, atau event di dalamnya.')
                            ->options(fn (Forms\Get $get) => CsrProgram::query()
                                ->whereNull('parent_id')
                                ->when(
                                    $get('category') === 'sports',
                                    fn (Builder $q) => $q->where('category', 'sports'),
                                    fn (Builder $q) => $q->where('category', '!=', 'sports'),
                                )
                                ->orderBy('title_id')
                                ->pluck('title_id', 'id')
                                ->all())
                            ->placeholder('— Halaman / kartu utama —')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Judul & alamat')
                    ->schema([
                        Forms\Components\TextInput::make('title_id')
                            ->label('Judul / Nama (ID)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('title_en')
                            ->label('Title / Name (EN)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug halaman detail')
                            ->helperText('Alamat halaman: /csr/{slug} — mis. "basketball". Kosongkan bila entri ini tidak punya halaman sendiri; slug dibuat otomatis dari judul bila slug dan link sama-sama kosong.')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => ! $isBlock($get)),
                        Forms\Components\TextInput::make('link')
                            ->label('Link "Pelajari Lebih Lanjut" (opsional)')
                            ->helperText('Tujuan tombol di kartu, dan link "Lihat Semua" di bawah galeri halaman detail. Isi hanya bila tujuannya di luar situs ini.')
                            ->url()
                            ->maxLength(255)
                            ->visible($isTopLevel),
                        Forms\Components\TextInput::make('sort')
                            ->label('Urutan')
                            ->helperText('Angka kecil tampil lebih dulu.')
                            ->numeric()
                            ->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
                    ])
                    ->columns(2),

                // Bukan hanya kartu utama: sub-topik governance (mis. Komite
                // Audit) juga punya halaman detail sendiri dan memakai tata
                // letak "Dewan / Komite". Yang tidak punya halaman detail
                // hanyalah blok tim/event.
                Forms\Components\Section::make('Tampilan halaman detail')
                    ->visible(fn (Forms\Get $get) => ! $isBlock($get))
                    ->schema([
                        Forms\Components\Select::make('layout')
                            ->label('Tata Letak Halaman Detail')
                            ->helperText('"Slider Program" memakai banner + intro + slider sub-program (mis. Social Care); "Galeri Foto" memakai grid foto lengkap dengan keterangan tiap foto; "Galeri Tim" memakai tata letak Basketball — banner + deskripsi + galeri 6 foto per halaman tanpa keterangan (mis. Environmental, Education), dan halaman itu dapat berisi event; "Dewan / Komite" memakai intro + grid Komite Audit & Corporate Secretary; "Artikel" memakai konten + formulir kontak.')
                            ->options([
                                'default' => 'Artikel + Formulir Kontak (default)',
                                'gallery' => 'Galeri Foto (grid + keterangan)',
                                'sports' => 'Galeri Tim (seperti Basketball, 6 foto/halaman)',
                                'slider' => 'Slider Program (mis. Social Care)',
                                'board' => 'Dewan / Komite (mis. Komite Audit)',
                            ])
                            ->live()
                            ->default('default')
                            ->native(false),
                    ]),

                Forms\Components\Section::make('Teks')
                    ->schema([
                        Forms\Components\Textarea::make('body_id')
                            ->label('Deskripsi Kartu / Banner (ID)')
                            ->helperText('Teks singkat di kartu CSR, juga dipakai sebagai subjudul banner halaman detail.')
                            ->rows(3)
                            ->visible($isTopLevel)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('body_en')
                            ->label('Card / Banner Description (EN)')
                            ->rows(3)
                            ->visible($isTopLevel)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('content_id')
                            ->label(fn (Forms\Get $get) => $isBlock($get) ? 'Deskripsi (ID)' : 'Isi Halaman Detail (ID)')
                            ->helperText(fn (Forms\Get $get) => $isBlock($get)
                                ? 'Paragraf yang tampil di samping nama tim / event.'
                                : 'Konten lengkap untuk halaman detail /csr/{slug}.')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('content_en')
                            ->label(fn (Forms\Get $get) => $isBlock($get) ? 'Description (EN)' : 'Detail Page Content (EN)')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('content2_id')
                            ->label('Isi Bawah Grid — Tugas & Wewenang (ID)')
                            ->helperText('Tata letak "Dewan / Komite" saja: teks di bawah grid anggota.')
                            ->visible(fn (Forms\Get $get) => $get('layout') === 'board')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('content2_en')
                            ->label('Below-Grid Content — Duties & Authority (EN)')
                            ->visible(fn (Forms\Get $get) => $get('layout') === 'board')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Gambar')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label(fn (Forms\Get $get) => $isBlock($get) ? 'Logo / Gambar' : 'Banner / Gambar Kartu')
                            ->image(),

                        // ⚠️ DUA komponen TIDAK BOLEH memakai satu statePath.
                        // Percobaan pertama mengikat keduanya ke `gallery`:
                        // Filament menghidrasi keduanya, FileUpload menerima
                        // item berbentuk array milik Repeater dan melempar
                        // "Argument #2 ($file) must be of type string, array
                        // given" — seluruh bagian Gambar gagal dirender tanpa
                        // pesan apa pun di layar.
                        //
                        // Karena itu uploader foto polos memakai nama khusus
                        // formulir `gallery_files` (pola yang sama dengan
                        // `top_category_id` di ProductResource) dan dipetakan
                        // kembali ke kolom `gallery` oleh Create/EditCsrProgram.
                        Forms\Components\FileUpload::make('gallery_files')
                            ->label('Galeri Foto')
                            ->helperText('Grid foto, 6 per halaman. Seret untuk mengubah urutan — urutan inilah yang menentukan isi Halaman 1, 2, dan seterusnya. Tata letak ini tidak menampilkan keterangan foto.')
                            ->image()->multiple()->reorderable()->appendFiles()
                            // Tata letak grid, bukan daftar menurun. Baris Golf
                            // menyimpan 30 foto; sebagai daftar vertikal satu
                            // foto per baris, mencari satu foto berarti
                            // menggulir 30 layar. Grid memberi 2 kolom, 3 pada
                            // layar >= 1024px, dan Filament otomatis memakai
                            // rasio 1:1 untuk petaknya (getItemPanelAspectRatio).
                            ->panelLayout('grid')
                            ->visible($isBlock)
                            ->dehydrated($isBlock)
                            // Kolomnya bernama lain, jadi isinya diambil sendiri
                            // dari record. Galeri lama dapat menyimpan
                            // {image, caption_*} — ratakan agar tidak kosong.
                            ->afterStateHydrated(function (Forms\Components\FileUpload $component, ?CsrProgram $record) {
                                $component->state(collect($record?->gallery ?? [])
                                    ->map(fn ($g) => is_array($g) ? ($g['image'] ?? null) : $g)
                                    ->filter()
                                    ->values()
                                    ->all());
                            })
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('gallery')
                            ->label('Galeri Foto (dengan keterangan)')
                            ->helperText('Foto grid di halaman detail. Setiap foto punya keterangan sendiri.')
                            ->visible(fn (Forms\Get $get) => ! $isBlock($get))
                            // Baris lama bisa menyimpan path polos; bungkus agar
                            // Repeater tidak menerima string di posisi array.
                            ->formatStateUsing(fn ($state) => collect($state ?? [])
                                ->map(fn ($g) => is_array($g) ? $g : ['image' => $g])
                                ->values()
                                ->all())
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
                            // `grid()` menata KARTU-nya berdampingan; `columns()`
                            // menata isi di DALAM satu kartu. Kartu selebar
                            // setengah layar terlalu sempit untuk dua kolom
                            // keterangan, jadi isinya ditumpuk satu kolom.
                            ->grid(2)
                            ->columns(1)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['caption_id'] ?? null)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                Tables\Columns\TextColumn::make('title_id')
                    ->label('Judul')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('entry_type')
                    ->label('Jenis')
                    ->badge()
                    ->state(fn (CsrProgram $record) => static::entryType($record)),
                Tables\Columns\TextColumn::make('parent.title_id')
                    ->label('Bagian dari')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Gambar'),
                Tables\Columns\TextColumn::make('gallery')
                    ->label('Foto')
                    ->state(fn (CsrProgram $record) => count($record->gallery ?? [])),
                Tables\Columns\TextColumn::make('sort')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
                // Pola satu klik yang sama dengan "Unggulan" di Penghargaan dan
                // "Tampil di halaman" di Dewan -- menerbitkan tidak perlu
                // membuka record.
                Tables\Columns\ToggleColumn::make('is_published')
                    ->label('Terbit'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'esg' => 'ESG',
                        'health_campaign' => 'Health Campaign',
                        'sports' => 'Olahraga',
                    ]),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Status terbit')
                    ->placeholder('Semua')
                    ->trueLabel('Terbit')
                    ->falseLabel('Draf'),
            ])
            ->emptyStateHeading('Belum ada entri CSR')
            ->emptyStateDescription('Kartu program, halaman detail, tim olahraga dan event untuk halaman Sustainability.')
            ->emptyStateIcon('heroicon-o-sparkles')
            ->actions([
                Tables\Actions\ViewAction::make()->slideOver(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('pratinjau')
                    ->label('Pratinjau')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    // Tautan bertanda tangan, berlaku 24 jam: draf bisa
                    // ditunjukkan ke orang yang tidak punya akun CMS tanpa
                    // menerbitkannya lebih dulu.
                    ->url(fn (CsrProgram $record) => static::previewUrl($record))
                    ->openUrlInNewTab()
                    // Program tanpa slug tidak punya halaman detail untuk dibuka.
                    ->visible(fn (CsrProgram $record) => filled($record->slug)),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Tautan pratinjau bertanda tangan ke halaman detail CSR.
     *
     * Nama route bersufiks locale (csr.show.id / csr.show.en) sesuai
     * CLAUDE.md §9 -- route('csr.show') sudah tidak ada.
     */
    public static function previewUrl(CsrProgram $record): string
    {
        return URL::temporarySignedRoute(
            'csr.show.'.config('app.locale'),
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
            'index' => Pages\ListCsrPrograms::route('/'),
            'create' => Pages\CreateCsrProgram::route('/create'),
            'edit' => Pages\EditCsrProgram::route('/{record}/edit'),
        ];
    }
}
