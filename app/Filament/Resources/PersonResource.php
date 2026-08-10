<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonResource\Pages;
use App\Filament\Resources\PersonResource\RelationManagers;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static ?string $navigationGroup = 'Tentang Kami';

    protected static ?string $navigationLabel = 'Dewan';

    protected static ?int $navigationSort = 3;

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
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role_en')
                    ->searchable(),
                Tables\Columns\TextColumn::make('group')
                    ->searchable(),
                // Editable straight from the list — flipping it off takes the
                // member off the page without deleting the record.
                Tables\Columns\ToggleColumn::make('show_on_page')
                    ->label('Tampil di halaman'),
                Tables\Columns\ImageColumn::make('photo'),
                Tables\Columns\TextColumn::make('sort')
                    ->label('Urutan')
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
            // Order is set by dragging the handle, not by typing a number:
            // ->reorderable writes `sort` for every affected row itself, so the
            // numbers stay contiguous and two members can never share one.
            ->defaultSort('sort')
            ->reorderable('sort')
            ->paginated(false)
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
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }
}
