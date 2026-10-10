<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Every save keeps the previous text, so a document's history can be read and restored. */
    public function up(): void
    {
        Schema::create('project_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('project_documents')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->unique(['document_id', 'version']);
        });

        Schema::table('project_documents', function (Blueprint $table) {
            $table->foreignUuid('last_edited_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('last_edited_by');
        });
        Schema::dropIfExists('project_document_versions');
    }
};
