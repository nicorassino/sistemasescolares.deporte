<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete()->cascadeOnUpdate();
            $table->enum('type', ['tuition', 'enrollment', 'other'])->default('tuition');
            $table->string('period', 10); // e.g. YYYY-MM
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->dateTime('issued_at');
            $table->dateTime('paid_at')->nullable();
            $table->string('receipt_number', 50)->nullable();
            $table->timestamps();

            $table->index(['student_id', 'period'], 'idx_fees_student_period');
            $table->index('status', 'idx_fees_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};

