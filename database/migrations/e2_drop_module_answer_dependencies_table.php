<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('module_answer_dependencies')) {
            Schema::dropIfExists('module_answer_dependencies');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('module_answer_dependencies')) {
            Schema::create('module_answer_dependencies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('methodology_id')->constrained('methodology')->onDelete('cascade');
                $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
                $table->foreignId('answer_id')->constrained('answers')->onDelete('cascade');
                $table->foreignId('dependent_question_id')->constrained('questions')->onDelete('cascade');
                $table->timestamps();
                $table->unique(['methodology_id', 'module_id', 'answer_id'], 'module_answer_dep_unique');
            });
        }
    }
};


