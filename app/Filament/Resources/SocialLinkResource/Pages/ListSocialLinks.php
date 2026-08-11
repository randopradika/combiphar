<?php

namespace App\Filament\Resources\SocialLinkResource\Pages;

use App\Filament\Concerns\HasGalleryToggle;
use App\Filament\Resources\SocialLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSocialLinks extends ListRecords
{
    use HasGalleryToggle;

    protected static string $resource = SocialLinkResource::class;

    /**
     * Ikon media sosial berukuran kecil dan jumlahnya sedikit; tabel tetap
     * menjadi tampilan awal, galeri tersedia lewat tombol.
     */
    protected function galleryByDefault(): bool
    {
        return false;
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getGalleryToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
