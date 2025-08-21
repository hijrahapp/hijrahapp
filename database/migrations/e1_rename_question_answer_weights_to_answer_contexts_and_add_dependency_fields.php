<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('question_answer_weights') && !Schema::hasTable('answer_contexts')) {
            Schema::rename('question_answer_weights', 'answer_contexts');
        }

        if (Schema::hasTable('answer_contexts')) {
            Schema::table('answer_contexts', function (Blueprint $table) {
                if (!Schema::hasColumn('answer_contexts', 'dependent_context_type')) {
                    $table->enum('dependent_context_type', ['methodology_question', 'pillar_question', 'module_question'])
                        ->nullable()
                        ->after('weight');
                }
                if (!Schema::hasColumn('answer_contexts', 'dependent_context_id')) {
                    $table->unsignedBigInteger('dependent_context_id')->nullable()->after('dependent_context_type');
                }
                $table->index(['dependent_context_type', 'dependent_context_id'], 'answer_contexts_dependent_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('answer_contexts')) {
            Schema::table('answer_contexts', function (Blueprint $table) {
                if (Schema::hasColumn('answer_contexts', 'dependent_context_id')) {
                    $table->dropIndex('answer_contexts_dependent_idx');
                    $table->dropColumn('dependent_context_id');
                }
                if (Schema::hasColumn('answer_contexts', 'dependent_context_type')) {
                    $table->dropColumn('dependent_context_type');
                }
            });
        }

        if (Schema::hasTable('answer_contexts') && !Schema::hasTable('question_answer_weights')) {
            Schema::rename('answer_contexts', 'question_answer_weights');
        }
    }
};


