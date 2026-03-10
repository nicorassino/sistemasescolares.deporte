<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_id')->constrained('fees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('tutor_id')->constrained('tutors')->restrictOnDelete()->cascadeOnUpdate();
            $table->decimal('amount_reported', 10, 2);
            $table->date('paid_on_date');
            $table->enum('status', ['pending_review', 'approved', 'rejected'])->default('pending_review');
            $table->string('evidence_file_path', 255);
            $table->unsignedInteger('evidence_file_size');
            $table->string('evidence_mime_type', 100);
            $table->string('bank_reference', 100)->nullable();
            $table->text('admin_comment')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->dateTime('reviewed_at')->nullable();
            $table->dateTime('archived_at')->nullable();
            $table->timestamps();

            $table->index('status', 'idx_payments_status');
            $table->index('archived_at', 'idx_payments_archived');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

