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
        Schema::create('objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', [
                'journal', 'article', 'advice', 'daily_mission', 
                'quiz', 'video', 'audio', 'book', 'challenge'
            ]);
            $table->integer('time_to_finish');
            $table->enum('time_type', ['hours', 'days', 'weeks', 'months']);
            $table->json('type_specific_data')->nullable(); // Store type-specific attributes as JSON
            $table->integer('order')->default(0); // For ordering objectives within a program
            $table->timestamps();
            
            $table->index(['program_id', 'type']);
            $table->index(['program_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objectives');
    }
};
