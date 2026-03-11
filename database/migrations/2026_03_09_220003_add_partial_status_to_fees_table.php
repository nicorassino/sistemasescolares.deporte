<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE fees MODIFY COLUMN status ENUM('pending', 'paid', 'overdue', 'cancelled', 'partial') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE fees MODIFY COLUMN status ENUM('pending', 'paid', 'overdue', 'cancelled') DEFAULT 'pending'");
    }
};
