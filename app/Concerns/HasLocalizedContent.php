<?php

namespace App\Concerns;

trait HasLocalizedContent
{
    /**
     * Return the value of a paired `_id`/`_en` field for the active locale,
     * falling back to the app fallback locale when empty.
     */
    public function tr(string $field): ?string
    {
        $locale = app()->getLocale();
        $value = $this->{$field . '_' . $locale} ?? null;

        if ($value === null || $value === '') {
            $value = $this->{$field . '_' . config('app.fallback_locale')} ?? null;
        }

        return $value;
    }
}
