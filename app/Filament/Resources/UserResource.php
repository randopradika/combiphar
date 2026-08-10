<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Kelola akun CMS.
 *
 * Kolom `users.role` sudah menjaga akses panel sejak pengerasan produksi,
 * tetapi tidak ada satu pun formulir yang terhubung ke sana — satu-satunya
 * cara memberi atau mencabut akses adalah lewat tinker atau SQL. Menu ini
 * menutup celah itu.
 *
 * ⚠️ `role` sengaja BUKAN mass-assignable (lihat App\Models\User), jadi
 * Create/EditUser menuliskannya secara eksplisit; jangan menambahkannya ke
 * #[Fillable] hanya agar formulir ini lebih ringkas.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Pengguna & Akses';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label('Kata sandi')
                    ->password()
                    ->revealable()
                    ->helperText('Kosongkan untuk membiarkan kata sandi saat ini tidak berubah.')
                    ->required(fn (string $operation) => $operation === 'create')
                    // Kolomnya memakai cast 'hashed', jadi nilai polos di sini
                    // otomatis di-hash saat disimpan.
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->label('Peran')
                    ->options(User::ROLES)
                    ->default(User::ROLE_EDITOR)
                    ->required()
                    ->native(false)
                    ->helperText('Administrator dan Editor dapat membuka CMS. "Nonaktif" mencabut akses tanpa menghapus akunnya — nama dan riwayatnya tetap utuh.')
                    // Tanpa ini seorang administrator dapat menonaktifkan
                    // dirinya sendiri dan langsung terkunci di luar panel.
                    ->disabled(fn (?User $record) => $record?->is(auth()->user()) ?? false)
                    ->dehydrated(fn (?User $record) => ! ($record?->is(auth()->user()) ?? false)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => User::ROLES[$state] ?? $state)
                    ->color(fn (?string $state) => match ($state) {
                        User::ROLE_ADMIN => 'primary',
                        User::ROLE_EDITOR => 'gray',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Peran')
                    ->options(User::ROLES),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (User $record) => $record->is(auth()->user())),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
