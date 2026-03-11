<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->after('group_id')->constrained('teachers')->nullOnDelete()->cascadeOnUpdate();
            $table->decimal('paid_amount', 10, 2)->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn('paid_amount');
        });
    }
};
