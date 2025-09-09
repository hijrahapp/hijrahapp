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
        Schema::create('step_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('step_id')->constrained('steps')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('correct_answer_id')->nullable()->constrained('answers')->onDelete('set null');
            $table->integer('sequence')->default(0);
            $table->timestamps();

            $table->unique(['step_id', 'question_id']);
            $table->index(['step_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('step_question');
    }
};
