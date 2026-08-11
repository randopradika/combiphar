<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MilestoneResource\Pages;
use App\Models\Milestone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MilestoneResource extends Resource
{
    protected static ?string $model = Milestone::class;

    protected static ?string $navigationGroup = 'Halaman';

    protected static ?string $navigationLabel = 'Beranda: Linimasa Perjalanan';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $recordTitleAttribute = 'caption_id';

    /**
     * Fields the panel-wide search box looks at. Both language columns are
     * listed so a search works whichever language the editor thinks in.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['caption_id', 'caption_en'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('year')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('caption_id')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('caption_en')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('photo')->image()->imageEditor(),
                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('year')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('photo'),
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
            ->emptyStateHeading('Belum ada linimasa')
            ->emptyStateDescription('Slider perjalanan perusahaan di halaman Beranda.')
            ->emptyStateIcon('heroicon-o-flag')
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
            'index' => Pages\ListMilestones::route('/'),
            'create' => Pages\CreateMilestone::route('/create'),
            'edit' => Pages\EditMilestone::route('/{record}/edit'),
        ];
    }
}
