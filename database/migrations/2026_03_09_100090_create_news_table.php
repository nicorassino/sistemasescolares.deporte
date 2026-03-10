<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->text('body');
            $table->string('image_path', 255)->nullable();
            $table->string('external_link', 255)->nullable();
            $table->boolean('is_published')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->integer('sort_order')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};

