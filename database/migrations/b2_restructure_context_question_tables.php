<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop obsolete tables if they exist
        Schema::dropIfExists('question_flow');
        Schema::dropIfExists('question_answer_weights');
        Schema::dropIfExists('methodology_question');
        Schema::dropIfExists('pillar_question');
        Schema::dropIfExists('module_question');

        /* ------------------------------------------------------------
         | Methodology ↔ Question (many-to-many with its own PK)
         |-----------------------------------------------------------*/
        Schema::create('methodology_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('methodology_id')->constrained('methodology')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['methodology_id', 'question_id']);
        });

        /* ------------------------------------------------------------
         | Pillar ↔ Question
         |-----------------------------------------------------------*/
        Schema::create('pillar_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pillar_id')->constrained('pillars')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['pillar_id', 'question_id']);
        });

        /* ------------------------------------------------------------
         | Module ↔ Question
         |-----------------------------------------------------------*/
        Schema::create('module_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['module_id', 'question_id']);
        });

        /* ------------------------------------------------------------
         | Context-specific Answer Weights
         |-----------------------------------------------------------*/
        Schema::create('question_answer_weights', function (Blueprint $table) {
            $table->id();
            // Stores which context (methodology/pillar/module question pivot) this weight belongs to
            $table->enum('context_type', ['methodology_question', 'pillar_question', 'module_question']);
            $table->unsignedBigInteger('context_id'); // FK to the corresponding pivot row id
            $table->foreignId('answer_id')->constrained('answers')->onDelete('cascade');
            $table->decimal('weight', 8, 2);
            $table->timestamps();

            $table->unique(['context_type', 'context_id', 'answer_id'], 'qa_weight_unique_v2');
            $table->index(['context_type', 'context_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_answer_weights');
        Schema::dropIfExists('module_question');
        Schema::dropIfExists('pillar_question');
        Schema::dropIfExists('methodology_question');
    }
};
