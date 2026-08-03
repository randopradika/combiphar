<?php

use App\Models\Person;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Roles are picked from a per-group dropdown now, and the page orders members
 * by role, so the stored text has to match Person::ROLES exactly.
 *
 * Existing rows were typed by hand: the audit committee carried " audit
 * committee" (leading space, lower case) and the corporate secretary had an
 * empty role_id. Left alone, those rows would show an empty dropdown on edit
 * and sort to the bottom of their group.
 *
 * Matching is on `group` — a group with a single role can be set outright, and
 * multi-role groups match on a trimmed, lower-cased role in either language.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Person::ROLES as $group => $roles) {
            // Single-role groups: every member carries the same title.
            if (count($roles) === 1) {
                DB::table('people')->where('group', $group)->update([
                    'role_id' => $roles[0]['id'],
                    'role_en' => $roles[0]['en'],
                ]);

                continue;
            }

            foreach (DB::table('people')->where('group', $group)->get() as $row) {
                $en = strtolower(trim((string) $row->role_en));
                $id = strtolower(trim((string) $row->role_id));
                $match = collect($roles)->first(
                    fn ($r) => strtolower($r['en']) === $en || strtolower($r['id']) === $id
                );

                if ($match) {
                    DB::table('people')->where('id', $row->id)->update([
                        'role_id' => $match['id'],
                        'role_en' => $match['en'],
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Text-only cleanup. The previous values were inconsistent by
        // definition, so there is nothing worth restoring.
    }
};
