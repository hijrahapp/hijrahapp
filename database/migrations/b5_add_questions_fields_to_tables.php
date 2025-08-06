<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add fields to methodology table
        Schema::table('methodology', function (Blueprint $table) {
            $table->longText('questions_description')->nullable()->after('definition');
            $table->string('questions_estimated_time')->nullable()->after('questions_description');
            $table->integer('questions_count')->default(0)->after('questions_estimated_time');
            $table->longText('first_section_description')->nullable()->after('questions_count');
            $table->longText('second_section_description')->nullable()->after('first_section_description');
        });

        // Add fields to pillars table
        Schema::table('pillars', function (Blueprint $table) {
            $table->longText('questions_description')->nullable()->after('definition');
            $table->string('questions_estimated_time')->nullable()->after('questions_description');
            $table->integer('questions_count')->default(0)->after('questions_estimated_time');
        });

        // Add fields to modules table
        Schema::table('modules', function (Blueprint $table) {
            $table->longText('questions_description')->nullable()->after('definition');
            $table->string('questions_estimated_time')->nullable()->after('questions_description');
            $table->integer('questions_count')->default(0)->after('questions_estimated_time');
        });
    }

    public function down(): void
    {
        // Remove fields from methodology table
        Schema::table('methodology', function (Blueprint $table) {
            $table->dropColumn([
                'questions_description',
                'questions_estimated_time',
                'questions_count',
                'first_section_description',
                'second_section_description'
            ]);
        });

        // Remove fields from pillars table
        Schema::table('pillars', function (Blueprint $table) {
            $table->dropColumn([
                'questions_description',
                'questions_estimated_time',
                'questions_count'
            ]);
        });

        // Remove fields from modules table
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn([
                'questions_description',
                'questions_estimated_time',
                'questions_count'
            ]);
        });
    }
}; 