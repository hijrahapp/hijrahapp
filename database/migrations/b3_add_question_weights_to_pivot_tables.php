<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add weight column to methodology_question pivot table
        Schema::table('methodology_question', function (Blueprint $table) {
            $table->decimal('weight', 8, 2)->default(1.0)->after('question_id');
        });

        // Add weight column to pillar_question pivot table
        Schema::table('pillar_question', function (Blueprint $table) {
            $table->decimal('weight', 8, 2)->default(1.0)->after('question_id');
        });

        // Add weight column to module_question pivot table
        Schema::table('module_question', function (Blueprint $table) {
            $table->decimal('weight', 8, 2)->default(1.0)->after('question_id');
        });
    }

    public function down(): void
    {
        // Remove weight columns from pivot tables
        Schema::table('methodology_question', function (Blueprint $table) {
            $table->dropColumn('weight');
        });

        Schema::table('pillar_question', function (Blueprint $table) {
            $table->dropColumn('weight');
        });

        Schema::table('module_question', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
}; 