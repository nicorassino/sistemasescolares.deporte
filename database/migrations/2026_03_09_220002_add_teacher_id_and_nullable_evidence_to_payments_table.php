<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->after('tutor_id')->constrained('teachers')->nullOnDelete()->cascadeOnUpdate();
            $table->string('evidence_file_path', 255)->nullable()->change();
            $table->unsignedInteger('evidence_file_size')->nullable()->change();
            $table->string('evidence_mime_type', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->string('evidence_file_path', 255)->nullable(false)->change();
            $table->unsignedInteger('evidence_file_size')->nullable(false)->change();
            $table->string('evidence_mime_type', 100)->nullable(false)->change();
        });
    }
};
