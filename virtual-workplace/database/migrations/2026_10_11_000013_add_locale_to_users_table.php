<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The language each person last used, so notifications and emails
        // reach them in it (not in the language of whoever triggered them).
        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 5)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};
