<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('pillar_module')) {
            return;
        }
        Schema::table('pillar_module', function (Blueprint $table) {
            if (!Schema::hasColumn('pillar_module', 'number_of_questions')) {
                $table->integer('number_of_questions')->nullable()->after('module_id');
            }
            if (!Schema::hasColumn('pillar_module', 'weight')) {
                $table->decimal('weight', 8, 2)->nullable()->after('number_of_questions');
            }
            if (!Schema::hasColumn('pillar_module', 'minutes')) {
                $table->integer('minutes')->nullable()->after('weight');
            }
            if (!Schema::hasColumn('pillar_module', 'report')) {
                $table->text('report')->nullable()->after('minutes');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pillar_module')) {
            return;
        }
        Schema::table('pillar_module', function (Blueprint $table) {
            if (Schema::hasColumn('pillar_module', 'report')) {
                $table->dropColumn('report');
            }
            if (Schema::hasColumn('pillar_module', 'minutes')) {
                $table->dropColumn('minutes');
            }
            if (Schema::hasColumn('pillar_module', 'weight')) {
                $table->dropColumn('weight');
            }
            if (Schema::hasColumn('pillar_module', 'number_of_questions')) {
                $table->dropColumn('number_of_questions');
            }
        });
    }
};


