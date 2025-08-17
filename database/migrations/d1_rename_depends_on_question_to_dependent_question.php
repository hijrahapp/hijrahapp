<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('module_answer_dependencies') && Schema::hasColumn('module_answer_dependencies', 'depends_on_question_id')) {
            Schema::table('module_answer_dependencies', function (Blueprint $table) {
                $table->renameColumn('depends_on_question_id', 'dependent_question_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('module_answer_dependencies') && Schema::hasColumn('module_answer_dependencies', 'dependent_question_id')) {
            Schema::table('module_answer_dependencies', function (Blueprint $table) {
                $table->renameColumn('dependent_question_id', 'depends_on_question_id');
            });
        }
    }
};


