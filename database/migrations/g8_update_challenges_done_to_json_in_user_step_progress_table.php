<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add a temporary column to store JSON data
        Schema::table('user_step_progress', function (Blueprint $table) {
            $table->json('challenges_done_temp')->nullable()->after('challenges_done');
        });

        // Convert existing integer values to JSON arrays in the temporary column
        DB::statement('UPDATE user_step_progress SET challenges_done_temp = JSON_ARRAY() WHERE challenges_done IS NULL OR challenges_done = 0');
        DB::statement('UPDATE user_step_progress SET challenges_done_temp = JSON_ARRAY(challenges_done) WHERE challenges_done IS NOT NULL AND challenges_done > 0');

        // Drop the original column and rename the temp column
        Schema::table('user_step_progress', function (Blueprint $table) {
            $table->dropColumn('challenges_done');
        });

        Schema::table('user_step_progress', function (Blueprint $table) {
            $table->renameColumn('challenges_done_temp', 'challenges_done');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add a temporary integer column
        Schema::table('user_step_progress', function (Blueprint $table) {
            $table->integer('challenges_done_temp')->default(0)->after('challenges_done');
        });

        // Convert JSON arrays back to integer count
        DB::statement('UPDATE user_step_progress SET challenges_done_temp = JSON_LENGTH(challenges_done) WHERE challenges_done IS NOT NULL');
        DB::statement('UPDATE user_step_progress SET challenges_done_temp = 0 WHERE challenges_done IS NULL');

        // Drop the JSON column and rename the temp column
        Schema::table('user_step_progress', function (Blueprint $table) {
            $table->dropColumn('challenges_done');
        });

        Schema::table('user_step_progress', function (Blueprint $table) {
            $table->renameColumn('challenges_done_temp', 'challenges_done');
        });
    }
};
