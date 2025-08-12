<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Methodology new columns
        Schema::table('methodology', function (Blueprint $table) {
            $table->string('img_url')->nullable()->after('objectives');
            $table->longText('first_section_definition')->nullable()->after('first_section_description');
            $table->longText('first_section_objectives')->nullable()->after('first_section_definition');
            $table->string('first_section_img_url')->nullable()->after('first_section_objectives');
            $table->longText('second_section_definition')->nullable()->after('second_section_description');
            $table->longText('second_section_objectives')->nullable()->after('second_section_definition');
            $table->string('second_section_img_url')->nullable()->after('second_section_objectives');
        });

        // Pillars new columns
        Schema::table('pillars', function (Blueprint $table) {
            $table->string('img_url')->nullable()->after('objectives');
        });

        // Modules new columns
        Schema::table('modules', function (Blueprint $table) {
            $table->string('img_url')->nullable()->after('objectives');
        });
    }

    public function down(): void
    {
        Schema::table('methodology', function (Blueprint $table) {
            $table->dropColumn([
                'img_url',
                'first_section_definition',
                'first_section_objectives',
                'first_section_img_url',
                'second_section_definition',
                'second_section_objectives',
                'second_section_img_url',
            ]);
        });

        Schema::table('pillars', function (Blueprint $table) {
            $table->dropColumn(['img_url']);
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['img_url']);
        });
    }
};


