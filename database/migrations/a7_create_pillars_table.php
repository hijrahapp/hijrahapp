<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pillars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->text('definition');
            $table->longText('objectives')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pillars');
    }
}; 