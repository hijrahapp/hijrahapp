<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('methodology', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('definition');
            $table->longText('objectives')->nullable();
            $table->enum('type', ['simple', 'complex', 'twoSection']);
            $table->string('first_section_name')->nullable();
            $table->string('second_section_name')->nullable();
            $table->string('pillars_definition')->nullable();
            $table->string('modules_definition')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('methodology');
    }
}; 