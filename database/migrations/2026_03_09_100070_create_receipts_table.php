<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_id')->constrained('fees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('pdf_path', 255)->nullable();
            $table->dateTime('generated_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};

