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
        Schema::table('methodology_question', function (Blueprint $table) {
            if (!Schema::hasColumn('methodology_question', 'item_id')) {
                $table->unsignedBigInteger('item_id')->nullable()->after('sequence');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('methodology_question', function (Blueprint $table) {
            if (Schema::hasColumn('methodology_question', 'item_id')) {
                $table->dropColumn('item_id');
            }
        });
    }
};


