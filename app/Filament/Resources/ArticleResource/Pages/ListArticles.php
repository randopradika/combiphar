<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use App\Models\Page;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Sakelar tab "Investor Update" di halaman Berita. Menyembunyikan
            // TAB-nya sekaligus butir sub-menu Berita di navigasi (desktop +
            // mobile) — lihat HandleInertiaRequests::withoutInvestorUpdate().
            // Default tersembunyi (migrasi 2026_08_12_000003).
            Actions\Action::make('toggleInvestorTab')
                ->label(fn (): string => $this->investorTabShown()
                    ? 'Sembunyikan tab Investor Update'
                    : 'Tampilkan tab Investor Update')
                ->icon(fn (): string => $this->investorTabShown()
                    ? 'heroicon-o-eye-slash'
                    : 'heroicon-o-eye')
                ->color('gray')
                ->action(function (): void {
                    $news = Page::where('slug', 'news')->first();

                    if (! $news) {
                        return;
                    }

                    $news->show_investor_tab = ! $news->show_investor_tab;
                    $news->save();

                    Notification::make()
                        ->title($news->show_investor_tab
                            ? 'Tab Investor Update kini tampil di halaman Berita dan menu navigasi.'
                            : 'Tab Investor Update disembunyikan dari halaman Berita dan menu navigasi.')
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }

    private function investorTabShown(): bool
    {
        return (bool) Page::where('slug', 'news')->value('show_investor_tab');
    }
}
