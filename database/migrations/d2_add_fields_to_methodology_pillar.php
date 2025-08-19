<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('methodology_pillar', function (Blueprint $table) {
            if (!Schema::hasColumn('methodology_pillar', 'sequence')) {
                $table->unsignedInteger('sequence')->nullable()->after('pillar_id');
            }
            if (!Schema::hasColumn('methodology_pillar', 'number_of_modules')) {
                $table->unsignedInteger('number_of_modules')->nullable()->after('sequence');
            }
            if (!Schema::hasColumn('methodology_pillar', 'weight')) {
                $table->decimal('weight', 8, 2)->nullable()->after('number_of_modules');
            }
        });
    }

    public function down(): void
    {
        Schema::table('methodology_pillar', function (Blueprint $table) {
            if (Schema::hasColumn('methodology_pillar', 'weight')) {
                $table->dropColumn('weight');
            }
            if (Schema::hasColumn('methodology_pillar', 'number_of_modules')) {
                $table->dropColumn('number_of_modules');
            }
            if (Schema::hasColumn('methodology_pillar', 'sequence')) {
                $table->dropColumn('sequence');
            }
        });
    }
};


