<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->enum('channel', ['email'])->default('email');
            $table->string('type', 100);
            $table->string('subject', 190);
            $table->text('body');
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->string('provider_message_id', 150)->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('status', 'idx_notifications_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

