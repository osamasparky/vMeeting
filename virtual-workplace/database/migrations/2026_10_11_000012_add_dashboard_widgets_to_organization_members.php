<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Each person's dashboard widgets, in order, per organization.
        Schema::table('organization_members', function (Blueprint $table) {
            $table->json('dashboard_widgets')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('organization_members', function (Blueprint $table) {
            $table->dropColumn('dashboard_widgets');
        });
    }
};
