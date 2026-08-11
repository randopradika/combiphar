<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobVacancyResource\Pages;
use App\Models\JobVacancy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JobVacancyResource extends Resource
{
    protected static ?string $model = JobVacancy::class;

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Lowongan Kerja';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

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
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        Forms\Components\Section::make('Isi lowongan')
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
                        Forms\Components\Section::make('Status')
                            ->schema([
                                // Sama seperti kolom di daftar: menutup lowongan
                                // tidak perlu menghapus recordnya.
                                Forms\Components\Toggle::make('is_open')
                                    ->label('Lowongan terbuka')
                                    ->helperText('Nonaktif: lowongan tidak lagi tampil di tab Karir, tetapi datanya tetap tersimpan.')
                                    ->default(true),
                            ]),

                        Forms\Components\Section::make('Penempatan & lamaran')
                            ->schema([
                                Forms\Components\TextInput::make('location')
                                    ->label('Lokasi')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('apply_url')
                                    ->label('Tautan lamaran')
                                    ->helperText('Tujuan tombol "Lamar". Kosongkan bila lamaran tidak lewat tautan.')
                                    ->url()
                                    ->maxLength(255),
                            ]),
                    ]),

                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
            ]);
    }

    /**
     * Judul, departemen dan tiga blok teks untuk satu bahasa.
     *
     * Enam pasang bidang _id/_en berjajar menumpuk sebelumnya, empat di
     * antaranya editor teks kaya -- artinya versi Inggris dari "Kualifikasi"
     * berada beberapa layar di bawah versi Indonesianya.
     */
    private static function contentFields(string $locale): array
    {
        $isId = $locale === 'id';

        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make("title_{$locale}")
                    ->label($isId ? 'Nama posisi' : 'Position title')
                    ->required($isId)
                    ->maxLength(255),
                Forms\Components\TextInput::make("department_{$locale}")
                    ->label($isId ? 'Departemen' : 'Department')
                    ->maxLength(255),
            ]),
            Forms\Components\Textarea::make("summary_{$locale}")
                ->label($isId ? 'Deskripsi singkat (kartu)' : 'Short description (card)')
                ->rows(3)
                ->helperText($isId ? 'Ringkasan yang tampil di kartu lowongan halaman Karir.' : null)
                ->columnSpanFull(),
            Forms\Components\RichEditor::make("description_{$locale}")
                ->label($isId ? 'Tanggung jawab' : 'Responsibilities')
                ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link', 'undo', 'redo'])
                ->columnSpanFull(),
            Forms\Components\RichEditor::make("requirements_{$locale}")
                ->label($isId ? 'Kualifikasi & pengalaman' : 'Qualifications & experience')
                ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link', 'undo', 'redo'])
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title_en')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department_en')
                    ->searchable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_open')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort')
                    ->numeric()
                    ->sortable(),
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
            ->emptyStateHeading('Belum ada lowongan kerja')
            ->emptyStateDescription('Lowongan tampil di tab Karir pada halaman Karir & Kontak. Nonaktifkan status terbuka untuk menutup lowongan tanpa menghapusnya.')
            ->emptyStateIcon('heroicon-o-briefcase')
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
            'index' => Pages\ListJobVacancies::route('/'),
            'create' => Pages\CreateJobVacancy::route('/create'),
            'edit' => Pages\EditJobVacancy::route('/{record}/edit'),
        ];
    }
}
