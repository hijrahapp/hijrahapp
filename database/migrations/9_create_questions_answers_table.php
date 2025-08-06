<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('answer_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Ensure unique combination of question and answer
            $table->unique(['question_id', 'answer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions_answers');
    }
}; 