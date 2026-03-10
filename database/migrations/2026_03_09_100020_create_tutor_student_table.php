<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutor_student', function (Blueprint $table) {
            $table->foreignId('tutor_id')->constrained('tutors')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('relationship_type', 50)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->primary(['tutor_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_student');
    }
};

