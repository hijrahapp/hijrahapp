<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* === Methodology ↔ Pillar (many-to-many) === */
        Schema::create('methodology_pillar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('methodology_id')->constrained('methodology')->onDelete('cascade');
            $table->foreignId('pillar_id')->constrained('pillars')->onDelete('cascade');
            $table->enum('section', ['first', 'second'])->default('first');
            $table->timestamps();
            $table->unique(['methodology_id', 'pillar_id', 'section'], 'methodology_pillar_unique');
        });

        /* === Methodology ↔ Module (many-to-many) === */
        Schema::create('methodology_module', function (Blueprint $table) {
            $table->id();
            $table->foreignId('methodology_id')->constrained('methodology')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['methodology_id', 'module_id']);
        });

        /* === Pillar ↔ Module (many-to-many) === */
        Schema::create('pillar_module', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pillar_id')->constrained('pillars')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['pillar_id', 'module_id']);
        });

        /* === Pillar dependencies inside the same Methodology === */
        Schema::create('pillar_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('methodology_id')->constrained('methodology')->onDelete('cascade');
            $table->foreignId('pillar_id')->constrained('pillars')->onDelete('cascade');
            $table->foreignId('depends_on_pillar_id')->constrained('pillars')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['methodology_id', 'pillar_id', 'depends_on_pillar_id'], 'pillar_dep_unique');
        });

        /* === Question placement in Modules === */
        Schema::create('module_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['module_id', 'question_id']);
        });

        /* === Question placement in Pillars === */
        Schema::create('pillar_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pillar_id')->constrained('pillars')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['pillar_id', 'question_id']);
        });

        /* === Question placement in Methodologies === */
        Schema::create('methodology_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('methodology_id')->constrained('methodology')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['methodology_id', 'question_id']);
        });

        /* === Control question flow based on answer chosen === */
        Schema::create('question_flow', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('answer_id')->constrained('answers')->onDelete('cascade');
            $table->foreignId('next_question_id')->constrained('questions')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['question_id', 'answer_id'], 'question_flow_unique');
        });

        /* === Context-specific answer weight === */
        Schema::create('question_answer_weights', function (Blueprint $table) {
            $table->id();
            $table->enum('context_type', ['methodology', 'pillar', 'module']);
            $table->unsignedBigInteger('context_id');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('answer_id')->constrained('answers')->onDelete('cascade');
            $table->decimal('weight', 8, 2);
            $table->timestamps();
            $table->unique(['context_type', 'context_id', 'question_id', 'answer_id'], 'qa_weight_unique');
            $table->index(['context_type', 'context_id']);
        });

        /* === User answers submission === */
        Schema::create('user_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('context_type', ['methodology', 'pillar', 'module']);
            $table->unsignedBigInteger('context_id');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('answer_id')->constrained('answers')->onDelete('cascade');
            $table->timestamps();
            $table->index(['user_id', 'context_type', 'context_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_answers');
        Schema::dropIfExists('question_answer_weights');
        Schema::dropIfExists('question_flow');
        Schema::dropIfExists('methodology_question');
        Schema::dropIfExists('pillar_question');
        Schema::dropIfExists('module_question');
        Schema::dropIfExists('pillar_dependencies');
        Schema::dropIfExists('pillar_module');
        Schema::dropIfExists('methodology_module');
        Schema::dropIfExists('methodology_pillar');
    }
};