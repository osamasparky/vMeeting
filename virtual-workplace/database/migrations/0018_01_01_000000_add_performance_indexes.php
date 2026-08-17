<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the performance index additions.
     */
    public function up(): void
    {
        Schema::table('organization_members', function (Blueprint $table) {
            $table->index(['organization_id', 'status'], 'idx_org_members_org_status');
            $table->index(['user_id', 'status'], 'idx_org_members_user_status');
        });

        Schema::table('guest_invitations', function (Blueprint $table) {
            $table->index(['organization_id', 'created_at'], 'idx_guest_invitations_org_created');
        });

        Schema::table('furniture_items', function (Blueprint $table) {
            $table->index(['category_id', 'is_active'], 'idx_furniture_items_cat_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_members', function (Blueprint $table) {
            $table->dropIndex('idx_org_members_org_status');
            $table->dropIndex('idx_org_members_user_status');
        });

        Schema::table('guest_invitations', function (Blueprint $table) {
            $table->dropIndex('idx_guest_invitations_org_created');
        });

        Schema::table('furniture_items', function (Blueprint $table) {
            $table->dropIndex('idx_furniture_items_cat_active');
        });
    }
};
