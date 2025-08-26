<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('methodology', function (Blueprint $table) {
            $table->longText('report')->nullable()->after('questions_estimated_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('methodology', function (Blueprint $table) {
            $table->dropColumn('report');
        });
    }
};
