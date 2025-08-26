<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('methodology', function (Blueprint $table) {
            if (Schema::hasColumn('methodology', 'questions_brief')) {
                $table->dropColumn('questions_brief');
            }
        });
    }

    public function down(): void
    {
        Schema::table('methodology', function (Blueprint $table) {
            if (! Schema::hasColumn('methodology', 'questions_brief')) {
                $table->longText('questions_brief')->nullable()->after('questions_estimated_time');
            }
        });
    }
};
