<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Satu baris pada menu navigasi situs.
 *
 * Bentuk yang dipakai React tidak berubah — HandleInertiaRequests menyusun
 * ulang baris-baris ini menjadi struktur `nav.menus` yang sama persis dengan
 * yang dulu ditulis tangan di lang/{id,en}/site.php.
 */
class NavItem extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];

    protected $casts = [
        'is_head' => 'boolean',
    ];

    /** Bagian navigasi yang tersedia — sama dengan kunci menu di berkas bahasa. */
    public const SECTIONS = [
        'about' => 'Tentang Kami',
        'products' => 'Produk',
        'csr' => 'Keberlanjutan',
        'investor' => 'Investor',
        'news' => 'Berita',
        'contact' => 'Karir & Kontak',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }
}
