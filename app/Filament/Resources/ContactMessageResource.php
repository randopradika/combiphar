<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationGroup = 'Formulir & Pesan';

    protected static ?string $navigationLabel = 'Pesan Masuk';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Fields the panel-wide search box looks at. Both language columns are
     * listed so a search works whichever language the editor thinks in.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'subject'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('subject')
                    ->maxLength(255),
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Kotak masuk, bukan daftar record biasa.
     *
     * Sebelumnya satu-satunya cara membaca isi pesan adalah membuka formulir
     * Ubah -- nama, email dan pesan pengunjung tersaji sebagai kolom isian yang
     * bisa tertimpa satu ketukan tombol, dan ContactMessage sengaja TIDAK
     * dicatat di activity_log, jadi perubahan seperti itu tidak meninggalkan
     * jejak sama sekali. Kini barisnya dibaca lewat panel geser read-only dan
     * Ubah menjadi tindakan kedua yang dipilih dengan sadar.
     */
    public static function table(Table $table): Table
    {
        return $table
            // Kapan pesan masuk adalah kolom terpenting di layar ini, dan
            // justru itu yang tadinya disembunyikan secara default.
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Dari')
                    ->searchable()
                    ->weight(FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email disalin'),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable()
                    ->placeholder('(tanpa subjek)'),
                Tables\Columns\TextColumn::make('message')
                    ->label('Cuplikan')
                    ->limit(60)
                    ->tooltip(fn (ContactMessage $record) => $record->message)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diterima')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('diterima')
                    ->label('Rentang tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari')->label('Dari tanggal'),
                        Forms\Components\DatePicker::make('sampai')->label('Sampai tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['dari'] ?? null, fn (Builder $q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['sampai'] ?? null, fn (Builder $q, $d) => $q->whereDate('created_at', '<=', $d))),
            ])
            ->emptyStateHeading('Belum ada pesan masuk')
            ->emptyStateDescription('Pesan dari formulir kontak di halaman Karir & Kontak dan di halaman detail CSR muncul di sini.')
            ->emptyStateIcon('heroicon-o-inbox')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Baca')
                    ->slideOver(),
                // Membalas terjadi di aplikasi email, bukan di CMS. Tombol ini
                // hanya menyiapkan penerima dan subjeknya supaya alamatnya tidak
                // perlu disalin-tempel dari layar sebelah.
                Tables\Actions\Action::make('balas')
                    ->label('Balas')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('gray')
                    ->url(fn (ContactMessage $record) => 'mailto:'.$record->email
                        .'?subject='.rawurlencode('Re: '.($record->subject ?: 'Pesan Anda untuk Combiphar')))
                    ->visible(fn (ContactMessage $record) => filled($record->email)),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Pesan hanya lahir dari formulir di situs. Tombol "Buat" di sini tidak
     * pernah punya arti, dan menutupnya sekaligus menghapus satu-satunya cara
     * memasukkan pesan palsu ke dalam kotak masuk.
     */
    public static function canCreate(): bool
    {
        return false;
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
            'index' => Pages\ListContactMessages::route('/'),
            'create' => Pages\CreateContactMessage::route('/create'),
            'edit' => Pages\EditContactMessage::route('/{record}/edit'),
        ];
    }
}
