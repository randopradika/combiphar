<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Satu baris catatan perubahan konten.
 *
 * Hanya ditulis oleh App\Observers\ActivityObserver dan tidak pernah disunting:
 * catatan yang bisa diubah tidak ada gunanya sebagai jejak.
 */
class Activity extends Model
{
    protected $table = 'activity_log';

    /** Tabel ini hanya punya created_at. */
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'changed_fields' => 'array',
        'created_at' => 'datetime',
    ];

    /** Nama tipe konten yang terbaca manusia, mis. "Article". */
    public function subjectName(): string
    {
        return class_basename((string) $this->subject_type);
    }

    public function eventLabel(): string
    {
        return match ($this->event) {
            'created' => 'Dibuat',
            'updated' => 'Diubah',
            'deleted' => 'Dihapus',
            default => (string) $this->event,
        };
    }

    /** Daftar nama field yang berubah, untuk kolom ringkas di tabel. */
    public function changedFields(): array
    {
        return is_array($this->changed_fields) ? array_keys($this->changed_fields) : [];
    }

    /**
     * Rincian sebelum/sesudah untuk tooltip. Nilai panjang dipotong -- yang
     * dicari editor biasanya "field mana yang berubah", bukan isi lengkapnya.
     */
    public function changeSummary(): ?string
    {
        if (! is_array($this->changed_fields) || $this->changed_fields === []) {
            return null;
        }

        $lines = [];

        foreach ($this->changed_fields as $field => $pair) {
            $old = $this->shorten($pair['old'] ?? null);
            $new = $this->shorten($pair['new'] ?? null);
            $lines[] = "{$field}: {$old} -> {$new}";
        }

        return implode("\n", $lines);
    }

    private function shorten(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '(kosong)';
        }

        if (is_array($value)) {
            $value = json_encode($value);
        }

        $value = trim(strip_tags((string) $value));

        return mb_strlen($value) > 60 ? mb_substr($value, 0, 60) . '...' : $value;
    }
}
