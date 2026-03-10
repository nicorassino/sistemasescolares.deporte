<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_student', function (Blueprint $table) {
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->primary(['group_id', 'student_id', 'from_date']);
            $table->index(['student_id', 'is_current'], 'idx_group_student_student_current');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_student');
    }
};

