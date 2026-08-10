<?php

namespace App\Filament\Resources\CsrProgramResource\Pages;

use App\Filament\Resources\CsrProgramResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCsrPrograms extends ListRecords
{
    protected static string $resource = CsrProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * Tiap tab memakai penyaring yang persis sama dengan salah satu dari empat
     * menu lama, sehingga isi tiap tab identik dengan menu yang digantikannya —
     * bedanya sekarang satu baris dapat berpindah jenis tanpa berpindah menu.
     */
    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua'),

            // dulu: "Program CSR"
            'program' => Tab::make('Program utama')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('category', '!=', 'sports')
                    ->whereNull('parent_id')),

            // dulu: "Dokumen Governance"
            'governance' => Tab::make('Dokumen governance')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereHas('parent', fn (Builder $q) => $q->where('slug', 'governance'))),

            // dulu: "Olahraga" (halaman olahraga + blok tim)
            'olahraga' => Tab::make('Olahraga')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'sports')),

            // dulu: "Galeri Acara"
            'event' => Tab::make('Event')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('category', '!=', 'sports')
                    ->whereNotNull('parent_id')
                    ->whereHas('parent', fn (Builder $p) => $p->where('layout', 'sports'))),

            // Sub-topik biasa (mis. anak dari Social Care). Dulu tercampur di
            // "Program CSR" bersama kartu utama; diberi tabnya sendiri agar
            // setiap baris punya tab, bukan hanya muncul di "Semua".
            'subtopik' => Tab::make('Sub-topik')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('category', '!=', 'sports')
                    ->whereNotNull('parent_id')
                    ->whereDoesntHave('parent', fn (Builder $p) => $p->where('slug', 'governance')->orWhere('layout', 'sports'))),
        ];
    }
}
