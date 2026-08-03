<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];

    protected $casts = [
        'show_on_page' => 'boolean',
    ];

    /**
     * Roles available per group, listed in the order they appear on the page.
     * The CMS offers exactly these, and scopeOrdered() ranks by this order —
     * a President Commissioner outranks a Commissioner however the rows were
     * dragged. Free text would break both, so keep the two in step.
     */
    public const ROLES = [
        'commissioners' => [
            ['id' => 'Presiden Komisaris', 'en' => 'President Commissioner'],
            ['id' => 'Komisaris', 'en' => 'Commissioner'],
            ['id' => 'Komisaris Independen', 'en' => 'Independent Commissioner'],
        ],
        'directors' => [
            ['id' => 'Presiden Direktur', 'en' => 'President Director'],
            ['id' => 'Direktur', 'en' => 'Director'],
        ],
        'audit_committee' => [
            ['id' => 'Komite Audit', 'en' => 'Audit Committee'],
        ],
        'corporate_secretary' => [
            ['id' => 'Corporate Secretary', 'en' => 'Corporate Secretary'],
        ],
    ];

    /** Dropdown options for a group: English value => bilingual label. */
    public static function roleOptions(?string $group): array
    {
        return collect(self::ROLES[$group] ?? [])
            ->mapWithKeys(fn ($r) => [$r['en'] => $r['id'].' ('.$r['en'].')'])
            ->all();
    }

    /** The Indonesian title paired with an English one. */
    public static function roleIdFor(?string $roleEn): ?string
    {
        foreach (self::ROLES as $roles) {
            foreach ($roles as $role) {
                if ($role['en'] === $roleEn) {
                    return $role['id'];
                }
            }
        }

        return null;
    }

    /** Members the CMS says to show on the public page. */
    public function scopeVisible($query)
    {
        return $query->where('show_on_page', true);
    }

    /**
     * Page order: role rank first, then the admin's drag order within a rank.
     * Every role_en across the groups is distinct, so one CASE covers them all
     * and each query is filtered to a single group anyway. Anything unmatched
     * (legacy free text) sorts last rather than silently jumping the queue.
     */
    public function scopeOrdered($query)
    {
        $case = 'CASE `role_en`';
        $bindings = [];

        foreach (self::ROLES as $roles) {
            foreach ($roles as $i => $role) {
                $case .= ' WHEN ? THEN ?';
                $bindings[] = $role['en'];
                $bindings[] = $i + 1;
            }
        }

        return $query->orderByRaw($case.' ELSE 99 END', $bindings)->orderBy('sort');
    }
}