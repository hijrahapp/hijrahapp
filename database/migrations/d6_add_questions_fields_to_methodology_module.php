<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('methodology_module')) {
            return;
        }
        Schema::table('methodology_module', function (Blueprint $table) {
            if (!Schema::hasColumn('methodology_module', 'questions_description')) {
                $table->longText('questions_description')->nullable()->after('report');
            }
            if (!Schema::hasColumn('methodology_module', 'questions_estimated_time')) {
                $table->integer('questions_estimated_time')->nullable()->after('questions_description');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('methodology_module')) {
            return;
        }
        Schema::table('methodology_module', function (Blueprint $table) {
            if (Schema::hasColumn('methodology_module', 'questions_estimated_time')) {
                $table->dropColumn('questions_estimated_time');
            }
            if (Schema::hasColumn('methodology_module', 'questions_description')) {
                $table->dropColumn('questions_description');
            }
        });
    }
};


