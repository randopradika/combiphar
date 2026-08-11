<?php

namespace App\Observers;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Mencatat setiap perubahan konten ke tabel activity_log.
 *
 * Satu observer dipakai untuk semua model yang dilacak (didaftarkan di
 * AppServiceProvider), sehingga menambah tipe konten baru cukup satu baris.
 */
class ActivityObserver
{
    /**
     * Field yang tidak boleh masuk catatan.
     *
     * Bukan sekadar kerapian: tanpa ini, mengubah kata sandi akan menyimpan
     * hash lama DAN baru di tabel yang bisa dibaca dari panel.
     */
    private const REDACTED = ['password', 'remember_token', 'api_token'];

    /** Kolom yang berubah di hampir setiap penyimpanan dan tidak informatif. */
    private const IGNORED = ['updated_at', 'created_at'];

    public function created(Model $model): void
    {
        $this->record('created', $model, []);
    }

    public function updated(Model $model): void
    {
        $changes = $this->diff($model);

        // Menyimpan tanpa mengubah apa pun (mis. membuka lalu menekan Simpan)
        // tidak perlu meninggalkan baris.
        if ($changes === []) {
            return;
        }

        $this->record('updated', $model, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->record('deleted', $model, []);
    }

    /** @return array<string, array{old: mixed, new: mixed}> */
    private function diff(Model $model): array
    {
        $changes = [];

        foreach ($model->getChanges() as $field => $new) {
            if (in_array($field, self::IGNORED, true)) {
                continue;
            }

            if (in_array($field, self::REDACTED, true)) {
                $changes[$field] = ['old' => '(disembunyikan)', 'new' => '(disembunyikan)'];

                continue;
            }

            $changes[$field] = [
                'old' => $this->plain($model->getOriginal($field)),
                'new' => $this->plain($new),
            ];
        }

        return $changes;
    }

    /** JSON hanya boleh berisi nilai skalar / array sederhana. */
    private function plain(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_object($value)) {
            return method_exists($value, '__toString') ? (string) $value : null;
        }

        return $value;
    }

    /** @param  array<string, array{old: mixed, new: mixed}>  $changes */
    private function record(string $event, Model $model, array $changes): void
    {
        $user = Auth::user();

        Activity::create([
            'event' => $event,
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'subject_label' => $this->label($model),
            'user_id' => $user?->getKey(),
            // Nama disalin, bukan dirujuk: akun bisa dihapus dan catatannya
            // harus tetap terbaca.
            'user_name' => $user?->name ?? $user?->email,
            'changed_fields' => $changes ?: null,
            'created_at' => now(),
        ]);
    }

    /**
     * Nama record yang terbaca manusia. Setiap tabel menamai kolomnya berbeda
     * (title_id, name_id, name, caption_id, question_id), jadi dicoba berurutan
     * alih-alih mengasumsikan satu pola.
     */
    private function label(Model $model): ?string
    {
        foreach (['title_id', 'title_en', 'name_id', 'name_en', 'name', 'caption_id', 'question_id', 'slug', 'email'] as $field) {
            $value = $model->getAttribute($field);

            if (is_string($value) && trim($value) !== '') {
                return mb_substr(trim($value), 0, 200);
            }
        }

        return null;
    }
}
