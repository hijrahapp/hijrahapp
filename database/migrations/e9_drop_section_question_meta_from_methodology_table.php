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
            $columns = [
                'first_section_number_of_questions',
                'first_section_minutes',
                'first_section_brief',
                'second_section_number_of_questions',
                'second_section_minutes',
                'second_section_brief',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('methodology', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('methodology', function (Blueprint $table) {
            if (! Schema::hasColumn('methodology', 'first_section_number_of_questions')) {
                $table->string('first_section_number_of_questions')->nullable();
            }
            if (! Schema::hasColumn('methodology', 'first_section_minutes')) {
                $table->string('first_section_minutes')->nullable();
            }
            if (! Schema::hasColumn('methodology', 'first_section_brief')) {
                $table->longText('first_section_brief')->nullable();
            }
            if (! Schema::hasColumn('methodology', 'second_section_number_of_questions')) {
                $table->string('second_section_number_of_questions')->nullable();
            }
            if (! Schema::hasColumn('methodology', 'second_section_minutes')) {
                $table->string('second_section_minutes')->nullable();
            }
            if (! Schema::hasColumn('methodology', 'second_section_brief')) {
                $table->longText('second_section_brief')->nullable();
            }
        });
    }
};
