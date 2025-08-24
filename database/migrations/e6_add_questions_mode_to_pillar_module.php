<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pillar_module', function (Blueprint $table) {
            if (!Schema::hasColumn('pillar_module', 'questions_mode')) {
                $table->enum('questions_mode', ['simple', 'dynamic'])->default('simple')->after('module_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pillar_module', function (Blueprint $table) {
            if (Schema::hasColumn('pillar_module', 'questions_mode')) {
                $table->dropColumn('questions_mode');
            }
        });
    }
};


