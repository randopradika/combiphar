<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;

/**
 * Isi footer situs -- satu baris saja.
 *
 * Bukan sebuah "halaman": tidak punya slug, rute, atau meta. Layar CMS-nya
 * tetap diletakkan di grup Halaman karena di situlah editor mencarinya.
 */
class FooterSetting extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];

    protected $casts = [
        // [{image, alt, height}] -- logo di kanan bawah footer, berurutan.
        'logos' => 'array',
    ];

    /**
     * Baris tunggal itu, dibuat bila belum ada.
     *
     * Dibuat malas supaya lingkungan yang barisnya terhapus tidak berujung pada
     * layar CMS yang error, melainkan formulir kosong yang bisa langsung diisi.
     */
    public static function current(): self
    {
        return static::query()->orderBy('id')->first() ?? static::create([]);
    }
}
