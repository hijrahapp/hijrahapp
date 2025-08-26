<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('methodology', function (Blueprint $table) {
            if (Schema::hasColumn('methodology', 'questions_count')) {
                $table->dropColumn('questions_count');
            }
            if (Schema::hasColumn('methodology', 'number_of_pillars')) {
                $table->dropColumn('number_of_pillars');
            }
            if (Schema::hasColumn('methodology', 'first_section_number_of_pillars')) {
                $table->dropColumn('first_section_number_of_pillars');
            }
            if (Schema::hasColumn('methodology', 'second_section_number_of_pillars')) {
                $table->dropColumn('second_section_number_of_pillars');
            }
        });

        if (Schema::hasTable('methodology_module')) {
            Schema::table('methodology_module', function (Blueprint $table) {
                if (Schema::hasColumn('methodology_module', 'number_of_questions')) {
                    $table->dropColumn('number_of_questions');
                }
            });
        }

        if (Schema::hasTable('pillar_module')) {
            Schema::table('pillar_module', function (Blueprint $table) {
                if (Schema::hasColumn('pillar_module', 'number_of_questions')) {
                    $table->dropColumn('number_of_questions');
                }
            });
        }

        if (Schema::hasTable('methodology_pillar')) {
            Schema::table('methodology_pillar', function (Blueprint $table) {
                if (Schema::hasColumn('methodology_pillar', 'number_of_modules')) {
                    $table->dropColumn('number_of_modules');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('methodology', function (Blueprint $table) {
            if (! Schema::hasColumn('methodology', 'questions_count')) {
                $table->integer('questions_count')->nullable()->after('questions_estimated_time');
            }
            if (! Schema::hasColumn('methodology', 'number_of_pillars')) {
                $table->text('number_of_pillars')->nullable()->after('pillars_definition');
            }
            if (! Schema::hasColumn('methodology', 'first_section_number_of_pillars')) {
                $table->string('first_section_number_of_pillars')->nullable()->after('first_section_objectives');
            }
            if (! Schema::hasColumn('methodology', 'second_section_number_of_pillars')) {
                $table->string('second_section_number_of_pillars')->nullable()->after('second_section_objectives');
            }
        });

        if (Schema::hasTable('methodology_module')) {
            Schema::table('methodology_module', function (Blueprint $table) {
                if (! Schema::hasColumn('methodology_module', 'number_of_questions')) {
                    $table->unsignedInteger('number_of_questions')->nullable()->after('module_id');
                }
            });
        }

        if (Schema::hasTable('pillar_module')) {
            Schema::table('pillar_module', function (Blueprint $table) {
                if (! Schema::hasColumn('pillar_module', 'number_of_questions')) {
                    $table->integer('number_of_questions')->nullable()->after('module_id');
                }
            });
        }

        if (Schema::hasTable('methodology_pillar')) {
            Schema::table('methodology_pillar', function (Blueprint $table) {
                if (! Schema::hasColumn('methodology_pillar', 'number_of_modules')) {
                    $table->unsignedInteger('number_of_modules')->nullable()->after('sequence');
                }
            });
        }
    }
};
