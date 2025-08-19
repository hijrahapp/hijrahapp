<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('methodology_module')) {
            return;
        }
        Schema::table('methodology_module', function (Blueprint $table) {
            if (Schema::hasColumn('methodology_module', 'minutes')) {
                $table->dropColumn('minutes');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('methodology_module')) {
            return;
        }
        Schema::table('methodology_module', function (Blueprint $table) {
            if (!Schema::hasColumn('methodology_module', 'minutes')) {
                $table->integer('minutes')->nullable()->after('weight');
            }
        });
    }
};


