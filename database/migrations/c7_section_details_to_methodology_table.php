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
            // Section 1 additional fields (only the new ones)
            $table->string('first_section_number_of_pillars')->nullable();
            $table->longText('first_section_pillars_definition')->nullable();
            $table->string('first_section_number_of_questions')->nullable();
            $table->string('first_section_minutes')->nullable();
            $table->longText('first_section_brief')->nullable();

            // Section 2 additional fields (only the new ones)
            $table->string('second_section_number_of_pillars')->nullable();
            $table->longText('second_section_pillars_definition')->nullable();
            $table->string('second_section_number_of_questions')->nullable();
            $table->string('second_section_minutes')->nullable();
            $table->longText('second_section_brief')->nullable();

            // General methodology fields (only if they don't exist)
            if (!Schema::hasColumn('methodology', 'number_of_pillars')) {
                $table->string('number_of_pillars')->nullable();
            }
            if (!Schema::hasColumn('methodology', 'active')) {
                $table->boolean('active')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('methodology', function (Blueprint $table) {
            // Drop Section 1 fields
            $table->dropColumn([
                'first_section_number_of_pillars',
                'first_section_pillars_definition',
                'first_section_number_of_questions',
                'first_section_minutes',
                'first_section_brief',
            ]);

            // Drop Section 2 fields
            $table->dropColumn([
                'second_section_number_of_pillars',
                'second_section_pillars_definition',
                'second_section_number_of_questions',
                'second_section_minutes',
                'second_section_brief',
            ]);

            // Drop general fields (only if they exist)
            if (Schema::hasColumn('methodology', 'number_of_pillars')) {
                $table->dropColumn('number_of_pillars');
            }
            if (Schema::hasColumn('methodology', 'active')) {
                $table->dropColumn('active');
            }
        });
    }
};
