<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Filament\Resources\PageResource\RelationManagers;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?string $navigationGroup = 'Halaman';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('meta_title_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('meta_title_en')
                    ->maxLength(255),
                Forms\Components\Textarea::make('meta_description_id')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('meta_description_en')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('banner_title_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('banner_title_en')
                    ->maxLength(255),
                Forms\Components\TextInput::make('banner_title2_id')
                    ->label('Banner title line 2 (ID)')
                    ->maxLength(255),
                Forms\Components\TextInput::make('banner_title2_en')
                    ->label('Banner title line 2 (EN)')
                    ->maxLength(255),
                Forms\Components\TextInput::make('banner_subtitle_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('banner_subtitle_en')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('banner_image')
                    ->image()
                    ->helperText('Banner untuk halaman selain Beranda (About, Products, dll).'),
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
                        Forms\Components\TextInput::make('manifesto_video')->label('Manifesto video URL')->url()->helperText('YouTube / Vimeo / MP4 link'),
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
                Forms\Components\Section::make('Footer — Copyright')
                    ->description('Teks copyright di footer. Ikon media sosial dikelola di menu "Media Sosial (Footer)".')
                    ->collapsible()
                    ->visible(fn (?Page $record) => $record?->slug === 'home')
                    ->schema([
                        Forms\Components\TextInput::make('footer_copyright_id')->label('Teks Copyright (ID)'),
                        Forms\Components\TextInput::make('footer_copyright_en')->label('Copyright text (EN)'),
                    ]),
            ]);
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
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
