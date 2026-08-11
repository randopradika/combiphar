<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    // Dulu resource ini disembunyikan dari navigasi dan hanya dapat dicapai
    // lewat tujuh pintasan "banner" yang masing-masing menunjuk satu record.
    // Akibatnya tidak ada daftar halaman di mana pun — dan tanpa daftar, tidak
    // ada pula tombol untuk membuat halaman baru.
    protected static ?string $navigationGroup = 'Halaman';

    protected static ?string $navigationLabel = 'Semua Halaman';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $modelLabel = 'Halaman';

    protected static ?string $pluralModelLabel = 'Halaman';

    protected static ?string $recordTitleAttribute = 'slug';

    public static function getGloballySearchableAttributes(): array
    {
        return ['slug', 'meta_title_id', 'meta_title_en'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Halaman')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([

                        Forms\Components\Tabs\Tab::make('Banner')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('banner_title_id')
                                        ->label('Judul banner (ID)')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('banner_title_en')
                                        ->label('Banner title (EN)')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('banner_title2_id')
                                        ->label('Judul baris 2 (ID)')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('banner_title2_en')
                                        ->label('Title line 2 (EN)')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('banner_subtitle_id')
                                        ->label('Subjudul (ID)')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('banner_subtitle_en')
                                        ->label('Subtitle (EN)')
                                        ->maxLength(255),
                                ]),
                                Forms\Components\FileUpload::make('banner_image')
                                    ->label('Gambar banner')
                                    ->image()
                                    ->imageEditor()
                                    ->helperText('Banner untuk halaman selain Beranda (About, Products, dll).'),
                            ]),

                        // Bidang khusus per halaman. Tiap bagian di bawah sudah
                        // punya visible() sendiri berdasarkan slug, jadi isi tab
                        // ini berbeda untuk tiap halaman -- dan itulah alasan
                        // bidangnya dikumpulkan di satu tempat ketimbang
                        // berserakan di antara banner dan SEO.
                        Forms\Components\Tabs\Tab::make('Konten halaman')
                            ->icon('heroicon-m-document-text')
                            ->schema([
                                Forms\Components\Section::make('Media Halaman Beranda (Home)')
                                    ->description('Diisi hanya untuk halaman dengan slug "home".')
                                    ->collapsible()
                                    ->visible(fn (?Page $record) => $record?->slug === 'home')
                                    ->schema([
                                        Forms\Components\FileUpload::make('hero_image')->label('Hero image')->image()->imageEditor(),
                                        Forms\Components\TextInput::make('hero_line1_id')->label('Hero line 1 (ID)')->default('Championing a'),
                                        Forms\Components\TextInput::make('hero_line1_en')->label('Hero line 1 (EN)')->default('Championing a'),
                                        Forms\Components\TextInput::make('hero_line2_id')->label('Hero line 2 - accent (ID)')->default('Healthy Tomorrow'),
                                        Forms\Components\TextInput::make('hero_line2_en')->label('Hero line 2 - accent (EN)')->default('Healthy Tomorrow'),
                                        Forms\Components\FileUpload::make('manifesto_image')->label('Manifesto background')->image()->imageEditor(),
                                        Forms\Components\TextInput::make('manifesto_title_id')->label('Manifesto title (ID)'),
                                        Forms\Components\TextInput::make('manifesto_title_en')->label('Manifesto title (EN)'),
                                        Forms\Components\FileUpload::make('manifesto_video_file')
                                            ->label('Manifesto video — upload MP4')
                                            ->acceptedFileTypes(['video/mp4'])
                                            ->directory('manifesto')
                                            ->maxSize(50 * 1024)
                                            ->helperText('Unggah file MP4 (maks 50 MB). Diprioritaskan; kosongkan untuk memakai URL di bawah.'),
                                        Forms\Components\TextInput::make('manifesto_video')->label('Manifesto video URL (opsional)')->url()->helperText('YouTube / Vimeo / MP4 link — dipakai hanya jika tidak ada file yang diunggah di atas.'),
                                        Forms\Components\FileUpload::make('cta_image')->label('CTA background')->image()->imageEditor(),
                                        Forms\Components\Textarea::make('cta_title_id')->label('CTA text (ID)')->rows(2),
                                        Forms\Components\Textarea::make('cta_title_en')->label('CTA text (EN)')->rows(2),
                                    ]),
                                Forms\Components\Section::make('Konten Halaman Tentang Kami (About)')
                                    ->description('Diisi hanya untuk halaman dengan slug "about".')
                                    ->collapsible()
                                    ->visible(fn (?Page $record) => $record?->slug === 'about')
                                    ->schema([
                                        Forms\Components\Textarea::make('intro_id')->label('Intro (ID)')->rows(3),
                                        Forms\Components\Textarea::make('intro_en')->label('Intro (EN)')->rows(3),
                                        Forms\Components\Textarea::make('vision_id')->label('Visi (ID)')->rows(2),
                                        Forms\Components\Textarea::make('vision_en')->label('Vision (EN)')->rows(2),
                                        Forms\Components\Textarea::make('mission_id')->label('Misi (ID)')->rows(2),
                                        Forms\Components\Textarea::make('mission_en')->label('Mission (EN)')->rows(2),
                                        Forms\Components\Textarea::make('values_id')->label('Nilai (ID)')->rows(2),
                                        Forms\Components\Textarea::make('values_en')->label('Values (EN)')->rows(2),
                                        Forms\Components\Section::make('Skala Kami & Kehadiran Kami (Our Scale & Presence)')
                                            ->description('Deskripsi (teks "Setiap penghargaan...") + kartu statistik pada section peta dunia.')
                                            ->schema([
                                                Forms\Components\FileUpload::make('presence_image')->label('Gambar Peta Dunia (World Map)')->image()->imageEditor()->helperText('Kosongkan untuk memakai peta default.'),
                                                Forms\Components\Textarea::make('presence_desc_id')->label('Deskripsi — teks "Setiap penghargaan..." (ID)')->rows(2),
                                                Forms\Components\Textarea::make('presence_desc_en')->label('Description (EN)')->rows(2),
                                                Forms\Components\TextInput::make('presence_popup_text_id')->label('Teks link Pop-up di bawah deskripsi (ID)')->helperText('Teks yang bisa diklik untuk membuka pop-up fasilitas produksi.'),
                                                Forms\Components\TextInput::make('presence_popup_text_en')->label('Pop-up link text (EN)'),
                                                Forms\Components\TextInput::make('stat1_value')->label('Statistik 1 — angka (mis. 1.600+)'),
                                                Forms\Components\TextInput::make('stat1_label_id')->label('Statistik 1 — label (ID)'),
                                                Forms\Components\TextInput::make('stat1_label_en')->label('Statistik 1 — label (EN)'),
                                                Forms\Components\TextInput::make('stat2_value')->label('Statistik 2 — angka (mis. 7)'),
                                                Forms\Components\TextInput::make('stat2_label_id')->label('Statistik 2 — label (ID)'),
                                                Forms\Components\TextInput::make('stat2_label_en')->label('Statistik 2 — label (EN)'),
                                            ]),
                                        Forms\Components\FileUpload::make('manufacturing_image')->label('Manufacturing image')->image()->imageEditor(),
                                        Forms\Components\TextInput::make('manufacturing_title_id')->label('Manufacturing title (ID)'),
                                        Forms\Components\TextInput::make('manufacturing_title_en')->label('Manufacturing title (EN)'),
                                        Forms\Components\Textarea::make('manufacturing_body_id')->label('Manufacturing body (ID)')->rows(4),
                                        Forms\Components\Textarea::make('manufacturing_body_en')->label('Manufacturing body (EN)')->rows(4),
                                        Forms\Components\FileUpload::make('international_image')->label('International Business image')->image()->imageEditor(),
                                        Forms\Components\TextInput::make('international_title_id')->label('International title (ID)'),
                                        Forms\Components\TextInput::make('international_title_en')->label('International title (EN)'),
                                        Forms\Components\Textarea::make('international_body_id')->label('International body (ID)')->rows(4),
                                        Forms\Components\Textarea::make('international_body_en')->label('International body (EN)')->rows(4),
                                    ]),
                                Forms\Components\Section::make('Konten Halaman CSR (Tanggung Jawab Sosial)')
                                    ->description('Diisi hanya untuk halaman dengan slug "csr". Deskripsi tampil di bawah judul banner baris 2 ("Corporate Citizenship...").')
                                    ->collapsible()
                                    ->visible(fn (?Page $record) => $record?->slug === 'csr')
                                    ->schema([
                                        Forms\Components\Textarea::make('intro_id')->label('Deskripsi / Intro (ID)')->rows(3),
                                        Forms\Components\Textarea::make('intro_en')->label('Deskripsi / Intro (EN)')->rows(3),
                                        Forms\Components\TextInput::make('health_title_id')
                                            ->label('Judul Health Campaign (ID)')
                                            ->helperText('Judul di atas kartu Health Campaign. Kosongkan judul dan deskripsi (ID dan EN) untuk menyembunyikannya.'),
                                        Forms\Components\TextInput::make('health_title_en')->label('Health Campaign heading (EN)'),
                                        Forms\Components\Textarea::make('health_desc_id')->label('Deskripsi Health Campaign (ID)')->rows(3),
                                        Forms\Components\Textarea::make('health_desc_en')->label('Health Campaign description (EN)')->rows(3),
                                    ]),
                                Forms\Components\Section::make('Program Kesejahteraan Karyawan (tab Karir)')
                                    ->description('Judul dan deskripsi di atas lingkaran program. Programnya sendiri dikelola di menu "Program Kesejahteraan Karyawan". Kosongkan judul dan deskripsi untuk menyembunyikan teks pengantarnya.')
                                    ->collapsible()
                                    ->visible(fn (?Page $record) => $record?->slug === 'contact')
                                    ->schema([
                                        Forms\Components\TextInput::make('wellness_title_id')->label('Judul (ID)'),
                                        Forms\Components\TextInput::make('wellness_title_en')->label('Title (EN)'),
                                        Forms\Components\Textarea::make('wellness_desc_id')->label('Deskripsi (ID)')->rows(3),
                                        Forms\Components\Textarea::make('wellness_desc_en')->label('Description (EN)')->rows(3),
                                    ]),
                                Forms\Components\Section::make('Pop-up "Recruitment Scam" (tab Karir)')
                                    ->description('Muncul setiap kali tab Karir dibuka. Matikan tombol di bawah untuk menghentikannya tanpa perlu deploy.')
                                    ->collapsible()
                                    ->visible(fn (?Page $record) => $record?->slug === 'contact')
                                    ->schema([
                                        Forms\Components\Toggle::make('scam_popup_enabled')
                                            ->label('Tampilkan pop-up')
                                            ->default(false),
                                        Forms\Components\TextInput::make('scam_title_id')->label('Judul (ID)'),
                                        Forms\Components\TextInput::make('scam_title_en')->label('Title (EN)'),
                                        Forms\Components\Repeater::make('scam_items')
                                            ->label('Poin Peringatan')
                                            ->helperText('Setiap poin tampil sebagai ikon dalam lingkaran dengan teks di bawahnya. Teks TEBAL tampil putih; teks MIRING tampil magenta (dipakai untuk nama domain, sesuai desain).')
                                            ->addActionLabel('Tambah Poin')
                                            ->reorderable()
                                            ->collapsible()
                                            ->defaultItems(0)
                                            ->columnSpanFull()
                                            ->schema([
                                                Forms\Components\FileUpload::make('icon')
                                                    ->label('Ikon')
                                                    ->helperText('PNG persegi berlatar transparan. Ikon tampil gelap di atas lingkaran lavender.')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->directory('scam'),
                                                Forms\Components\RichEditor::make('text_id')
                                                    ->label('Teks (ID)')
                                                    ->toolbarButtons(['bold', 'italic', 'undo', 'redo']),
                                                Forms\Components\RichEditor::make('text_en')
                                                    ->label('Text (EN)')
                                                    ->toolbarButtons(['bold', 'italic', 'undo', 'redo']),
                                            ]),
                                        Forms\Components\Textarea::make('scam_note_id')
                                            ->label('Catatan kecil (ID)')
                                            ->rows(3)
                                            ->helperText('Teks kecil di bagian bawah pop-up, mis. nomor Call Center dan jam layanan.'),
                                        Forms\Components\Textarea::make('scam_note_en')->label('Small print (EN)')->rows(3),
                                    ]),
                                Forms\Components\Section::make('Peringatan Penipuan Lowongan (tab Karir & Kontak)')
                                    ->description('Pita ungu-magenta di bagian bawah halaman. Judul tampil dalam huruf besar; tekan Enter untuk mengatur pergantian baris. Catatan diberi nomor 1, 2, 3 … secara otomatis.')
                                    ->collapsible()
                                    ->visible(fn (?Page $record) => $record?->slug === 'contact')
                                    ->schema([
                                        Forms\Components\Textarea::make('fraud_title_id')
                                            ->label('Judul (ID)')
                                            ->rows(2)
                                            ->helperText('Contoh: "HATI-HATI PENIPUAN" lalu Enter lalu "LOWONGAN KERJA".'),
                                        Forms\Components\Textarea::make('fraud_title_en')->label('Title (EN)')->rows(2),
                                        Forms\Components\Repeater::make('fraud_items')
                                            ->label('Catatan Bernomor')
                                            ->helperText('Kosongkan seluruh daftar untuk menyembunyikan catatan. Gunakan tebal untuk menyorot nomor telepon atau akun.')
                                            ->addActionLabel('Tambah Catatan')
                                            ->reorderable()
                                            ->collapsible()
                                            ->defaultItems(0)
                                            ->columnSpanFull()
                                            ->schema([
                                                Forms\Components\RichEditor::make('text_id')
                                                    ->label('Teks (ID)')
                                                    ->toolbarButtons(['bold', 'italic', 'link', 'undo', 'redo']),
                                                Forms\Components\RichEditor::make('text_en')
                                                    ->label('Text (EN)')
                                                    ->toolbarButtons(['bold', 'italic', 'link', 'undo', 'redo']),
                                            ]),
                                    ]),
                                // Copyright footer dulu berada di sini, di dalam record
                                // Beranda: satu-satunya isi di formulir ini yang tampil di
                                // SETIAP halaman, bukan di halaman ini saja. Sekarang
                                // seluruh isi footer ada di Halaman -> Footer.
                            ]),

                        // Meta dulunya bidang PERTAMA pada formulir 60 bidang ini,
                        // padahal paling jarang disunting -- dan tanpa penghitung
                        // karakter, meski layar Ringkasan SEO memang dibuat untuk
                        // menghitungnya. Layar yang mengukur dan layar yang
                        // menyunting akhirnya tidak pernah bertemu, dan ketujuh
                        // halaman berangkat tanpa satu pun meta description.
                        Forms\Components\Tabs\Tab::make('SEO')
                            ->icon('heroicon-m-magnifying-glass')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('meta_title_id')
                                        ->label('Meta title (ID)')
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->helperText(fn (?string $state) => static::metaHint($state, 60)),
                                    Forms\Components\TextInput::make('meta_title_en')
                                        ->label('Meta title (EN)')
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->helperText(fn (?string $state) => static::metaHint($state, 60)),
                                ]),
                                Forms\Components\Textarea::make('meta_description_id')
                                    ->label('Meta description (ID)')
                                    ->rows(3)
                                    ->live(onBlur: true)
                                    ->helperText(fn (?string $state) => static::metaHint($state, 160, 70))
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('meta_description_en')
                                    ->label('Meta description (EN)')
                                    ->rows(3)
                                    ->live(onBlur: true)
                                    ->helperText(fn (?string $state) => static::metaHint($state, 160, 70))
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Lanjutan')
                            ->icon('heroicon-m-cog-6-tooth')
                            ->schema([
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Penanda halaman yang dipakai kode program. Mengubahnya melepaskan seluruh isi di tab "Konten halaman" dari halaman ini.'),
                                Forms\Components\Toggle::make('under_development')
                                    ->label('Dalam Pengembangan — tampilkan "Segera Hadir"')
                                    ->helperText('Aktif: halaman Investor + tab "Investor Update" di Berita menampilkan konten "Segera Hadir". Nonaktif: menampilkan konten sebenarnya.')
                                    ->visible(fn (?Page $record) => $record?->slug === 'investor')
                                    ->default(false),
                                Forms\Components\Toggle::make('show_in_menu')
                                    ->label('Tampilkan menu "Investor" di navigasi')
                                    ->helperText('Nonaktif: menu Investor disembunyikan dari navigasi atas, menu mobile, dan footer. Halamannya tetap bisa dibuka lewat URL.')
                                    ->visible(fn (?Page $record) => $record?->slug === 'investor')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    /**
     * Penghitung karakter memakai ambang yang sama dengan layar Ringkasan SEO
     * (60 untuk judul, 70-160 untuk deskripsi), supaya kedua layar tidak pernah
     * memberi penilaian yang berbeda atas teks yang sama.
     */
    private static function metaHint(?string $state, int $max, ?int $min = null): string
    {
        $length = mb_strlen((string) $state);

        if ($length === 0) {
            return 'Kosong — mesin pencari akan mengarang cuplikannya sendiri.';
        }

        if ($length > $max) {
            return "{$length} karakter — terlalu panjang, akan terpotong (ideal maksimal {$max}).";
        }

        if ($min !== null && $length < $min) {
            return "{$length} karakter — terlalu pendek (ideal {$min}-{$max}).";
        }

        return "{$length} karakter — panjangnya pas.";
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('meta_title_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('meta_title_en')
                    ->searchable(),
                Tables\Columns\TextColumn::make('banner_title_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('banner_title_en')
                    ->searchable(),
                Tables\Columns\TextColumn::make('banner_subtitle_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('banner_subtitle_en')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('banner_image'),
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
                //
            ])
            ->emptyStateHeading('Belum ada halaman')
            ->emptyStateDescription('Setiap halaman situs punya satu record di sini untuk banner, teks dan meta SEO-nya.')
            ->emptyStateIcon('heroicon-o-document-duplicate')
            ->actions([
                Tables\Actions\ViewAction::make()->slideOver(),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
