<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Point the Environmental and Education detail pages at the Basketball layout.
 *
 * `layout = 'sports'` now renders CsrDetail programmes through SportsDetail:
 * banner + description + a gallery paginated 6 photos per page. Neither
 * programme has team children, so each becomes its own single block (see
 * PageController::csrShow).
 *
 * Ships as a migration rather than a CMS edit because csr_programs rows are
 * per-environment — the same reason 2026_07_29_000003 renamed a card this way.
 * Matched on `slug`, which is stable and routable (/csr/{slug}).
 */
return new class extends Migration
{
    /** @var list<string> */
    private const SLUGS = ['environmental', 'education'];

    public function up(): void
    {
        DB::table('csr_programs')
            ->whereIn('slug', self::SLUGS)
            ->update(['layout' => 'sports']);
    }

    /**
     * Deliberately a no-op. The previous values were per-environment — null on
     * Environmental, 'gallery' on Education here, but not guaranteed elsewhere —
     * so there is nothing safe to restore. Set the layout from the CMS instead;
     * it is a normal editable field ("Tata Letak Halaman Detail").
     */
    public function down(): void {}
};
