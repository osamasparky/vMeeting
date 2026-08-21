<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nickname')) {
                $table->string('nickname')->nullable()->after('name');
            }
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('user_profiles', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('user_profiles', 'hobbies')) {
                $table->text('hobbies')->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('user_profiles', 'skills')) {
                $table->text('skills')->nullable()->after('hobbies');
            }
            if (!Schema::hasColumn('user_profiles', 'social_links')) {
                $table->json('social_links')->nullable()->after('skills');
            }
            if (!Schema::hasColumn('user_profiles', 'notes')) {
                $table->text('notes')->nullable()->after('social_links');
            }
            if (!Schema::hasColumn('user_profiles', 'work_mode')) {
                $table->string('work_mode')->default('remote')->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['date_of_birth', 'hobbies', 'skills', 'social_links', 'notes', 'work_mode']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nickname']);
        });
    }
};
