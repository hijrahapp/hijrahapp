<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pillars', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('tags');
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('tags');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('tags');
        });
    }

    public function down(): void
    {
        Schema::table('pillars', function (Blueprint $table) {
            $table->dropColumn('active');
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('active');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
