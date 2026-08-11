<?php

namespace App\Console\Commands;

use App\Support\MediaScanner;
use Illuminate\Console\Command;

/**
 * Membangun ulang indeks Pustaka Media.
 *
 * Indeksnya adalah cuplikan keadaan, bukan sesuatu yang selalu mutakhir: berkas
 * bisa diunggah atau dilepas rujukannya kapan saja. Jalankan ini setelah impor
 * massal, atau pakai tombol "Pindai ulang" di panel.
 */
class ScanMedia extends Command
{
    protected $signature = 'media:scan';

    protected $description = 'Memindai disk publik dan mencatat setiap berkas beserta tempat pemakaiannya';

    public function handle(MediaScanner $scanner): int
    {
        $this->info('Memindai...');

        $stats = $scanner->scan();

        $this->table(
            ['Terindeks', 'Tidak terpakai', 'Dihapus dari indeks'],
            [[$stats['indexed'], $stats['unused'], $stats['removed']]],
        );

        return self::SUCCESS;
    }
}
