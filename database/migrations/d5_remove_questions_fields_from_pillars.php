<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pillars', function (Blueprint $table) {
            if (Schema::hasColumn('pillars', 'questions_estimated_time')) {
                $table->dropColumn('questions_estimated_time');
            }
            if (Schema::hasColumn('pillars', 'questions_description')) {
                $table->dropColumn('questions_description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pillars', function (Blueprint $table) {
            if (!Schema::hasColumn('pillars', 'questions_description')) {
                $table->longText('questions_description')->nullable()->after('definition');
            }
            if (!Schema::hasColumn('pillars', 'questions_estimated_time')) {
                $table->string('questions_estimated_time')->nullable()->after('questions_description');
            }
        });
    }
};


