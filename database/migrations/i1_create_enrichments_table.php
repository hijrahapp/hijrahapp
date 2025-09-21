<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enrichments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['health', 'money', 'family', 'spirituality']);
            $table->enum('type', ['article', 'video', 'audio', 'book', 'short-video']);
            $table->string('img_url');
            $table->text('header');
            $table->longText('content');
            $table->string('content_url')->nullable();
            $table->json('interests')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrichments');
    }
};
