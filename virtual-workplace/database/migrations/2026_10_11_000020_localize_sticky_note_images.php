<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Sticky notes once used images hot-linked from an outside asset host; this migration pointed
 * them at our own images in public/images/furniture/sticky/. It has run everywhere, and the
 * catalogue seed data now ships local paths, so there is nothing left for it to do. Kept (empty)
 * so the migrations table stays in step on servers where it already ran.
 */
return new class extends Migration
{
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
