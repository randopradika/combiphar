<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Satu berkas di disk 'public', beserta catatan dipakai di mana.
 *
 * Baris di sini adalah INDEKS, bukan sumber kebenaran: yang menentukan tetap
 * isi disk dan kolom-kolom path di tabel lain. MediaScanner membangunnya ulang.
 */
class MediaFile extends Model
{
    protected $guarded = [];

    protected $casts = [
        'used_by' => 'array',
        'file_modified_at' => 'datetime',
        'scanned_at' => 'datetime',
    ];

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function isUnused(): bool
    {
        return (int) $this->usage_count === 0;
    }

    /** "gambar", "video", "dokumen" -- dipakai sebagai filter dan badge. */
    public function kind(): string
    {
        return match (strtolower((string) $this->extension)) {
            'jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'ico', 'bmp', 'svg' => 'gambar',
            'mp4', 'webm', 'mov' => 'video',
            default => 'dokumen',
        };
    }

    /** Ringkasan "dipakai di mana" untuk tooltip, mis. "awards.image #12". */
    public function usageSummary(): ?string
    {
        $rows = $this->used_by;

        if (! is_array($rows) || $rows === []) {
            return null;
        }

        return collect($rows)
            ->map(function ($r) {
                // Rujukan dari kode tidak punya id baris; menuliskan "#?" hanya
                // menambah derau. Yang berguna justru nama berkasnya.
                if (($r['table'] ?? null) === 'kode') {
                    return 'kode: ' . ($r['column'] ?? '?');
                }

                $ref = ($r['table'] ?? '?') . '.' . ($r['column'] ?? '?');

                return isset($r['id']) ? $ref . ' #' . $r['id'] : $ref;
            })
            ->implode("\n");
    }
}
