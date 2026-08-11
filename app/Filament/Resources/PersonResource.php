<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonResource\Pages;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static ?string $navigationGroup = 'Profil Perusahaan';

    protected static ?string $navigationLabel = 'Dewan';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Fields the panel-wide search box looks at. Both language columns are
     * listed so a search works whichever language the editor thinks in.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'role_id', 'role_en'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Name and Grup share a row; Grup drives the Jabatan options.
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('group')
                        ->label('Grup')
                        ->options([
                            'commissioners' => 'Board of Commissioners',
                            'directors' => 'Board of Directors',
                            'audit_committee' => 'Komite Audit',
                            'corporate_secretary' => 'Corporate Secretary',
                        ])
                        ->required()
                        ->default('directors')
                        ->native(false)
                        // Options below depend on this, and the old pick would
                        // be invalid for the new group.
                        ->live()
                        ->afterStateUpdated(function (Forms\Set $set) {
                            $set('role_en', null);
                            $set('role_id', null);
                        }),
                ]),
                // One dropdown writes both language columns, so the pair can
                // never disagree. role_id follows from the chosen role_en.
                Forms\Components\Select::make('role_en')
                    ->label('Jabatan')
                    ->options(fn (Forms\Get $get) => Person::roleOptions($get('group')))
                    ->required()
                    ->native(false)
                    ->helperText('Pilihannya mengikuti Grup. Urutan tampil di halaman mengikuti jabatan ini — mis. Presiden Komisaris selalu di atas Komisaris.')
                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('role_id', Person::roleIdFor($state)))
                    ->live(),
                Forms\Components\Hidden::make('role_id'),
                Forms\Components\RichEditor::make('bio_id')->label('Bio (ID)')->columnSpanFull()->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link', 'undo', 'redo']),
                Forms\Components\RichEditor::make('bio_en')->label('Bio (EN)')->columnSpanFull()->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link', 'undo', 'redo']),
                Forms\Components\FileUpload::make('photo')->image()->imageEditor(),
                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Menyusun ulang dewan paling wajar dilakukan sambil melihat wajahnya,
        // jadi halaman ini terbuka sebagai galeri foto yang tetap bisa diseret.
        $livewire = $table->getLivewire();
        $gallery = method_exists($livewire, 'isGallery') && $livewire->isGallery();

        $table = $gallery
            ? $table->columns(static::galleryColumns())->contentGrid(['sm' => 2, 'lg' => 3, '2xl' => 4])
            : $table->columns(static::listColumns());

        return $table
            // Order is set by dragging the handle, not by typing a number:
            // ->reorderable writes `sort` for every affected row itself, so the
            // numbers stay contiguous and two members can never share one.
            ->defaultSort('sort')
            ->reorderable('sort')
            ->paginated(false)
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->label('Grup')
                    ->options([
                        'commissioners' => 'Board of Commissioners',
                        'directors' => 'Board of Directors',
                        'audit_committee' => 'Komite Audit',
                        'corporate_secretary' => 'Corporate Secretary',
                    ]),
                Tables\Filters\TernaryFilter::make('show_on_page')
                    ->label('Tampil di halaman'),
            ])
            ->emptyStateHeading('Belum ada anggota dewan')
            ->emptyStateDescription('Anggota tampil di halaman Tentang Kami, diurutkan menurut jabatan lalu urutan yang Anda susun di sini. Grup Komite Audit dan Corporate Secretary juga mengisi halaman CSR Komite Audit.')
            ->emptyStateIcon('heroicon-o-users')
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

    private static function listColumns(): array
    {
        return [
            Tables\Columns\ImageColumn::make('photo')
                ->label('')
                ->circular(),
            Tables\Columns\TextColumn::make('name')
                ->label('Nama')
                ->searchable()
                ->weight(FontWeight::SemiBold),
            Tables\Columns\TextColumn::make('role_id')
                ->label('Jabatan')
                ->searchable(),
            Tables\Columns\TextColumn::make('group')
                ->label('Grup')
                ->badge()
                ->color('gray')
                ->searchable(),
            // Editable straight from the list — flipping it off takes the
            // member off the page without deleting the record.
            Tables\Columns\ToggleColumn::make('show_on_page')
                ->label('Tampil di halaman'),
            Tables\Columns\TextColumn::make('updated_at')
                ->label('Diperbarui')
                ->since()
                ->dateTimeTooltip()
                ->sortable(),
        ];
    }

    private static function galleryColumns(): array
    {
        return [
            Tables\Columns\Layout\Stack::make([
                Tables\Columns\ImageColumn::make('photo')
                    ->height(190)
                    ->extraImgAttributes(['class' => 'w-full h-48 object-cover rounded-lg']),
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('name')
                        ->weight(FontWeight::SemiBold)
                        ->searchable()
                        ->wrap(),
                    Tables\Columns\TextColumn::make('role_id')
                        ->color('gray')
                        ->wrap(),
                    // Yang disembunyikan dari halaman harus terbaca dari kartunya,
                    // atau satu-satunya petunjuk hilang bersama kolom toggle.
                    Tables\Columns\TextColumn::make('show_on_page')
                        ->badge()
                        ->color('danger')
                        ->state(fn (Person $record) => $record->show_on_page ? null : 'Disembunyikan')
                        ->placeholder(''),
                ])->space(1),
            ])->space(2),
        ];
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
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }
}
