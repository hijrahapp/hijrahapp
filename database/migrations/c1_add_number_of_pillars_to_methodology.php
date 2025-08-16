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
            if (!Schema::hasColumn('methodology', 'number_of_pillars')) {
                $table->string('number_of_pillars')->nullable()->after('pillars_definition');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('methodology', function (Blueprint $table) {
            if (Schema::hasColumn('methodology', 'number_of_pillars')) {
                $table->dropColumn('number_of_pillars');
            }
        });
    }
};
