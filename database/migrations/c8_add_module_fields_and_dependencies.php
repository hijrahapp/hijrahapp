<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('methodology_module', function (Blueprint $table) {
            if (!Schema::hasColumn('methodology_module', 'number_of_questions')) {
                $table->unsignedInteger('number_of_questions')->nullable()->after('module_id');
            }
            if (!Schema::hasColumn('methodology_module', 'weight')) {
                $table->decimal('weight', 8, 2)->nullable()->after('number_of_questions');
            }
            if (!Schema::hasColumn('methodology_module', 'minutes')) {
                $table->unsignedInteger('minutes')->default(0)->after('weight');
            }
            if (!Schema::hasColumn('methodology_module', 'report')) {
                $table->text('report')->nullable()->after('minutes');
            }
        });

        if (!Schema::hasTable('module_dependencies')) {
            Schema::create('module_dependencies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('methodology_id')->constrained('methodology')->onDelete('cascade');
                $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
                $table->foreignId('depends_on_module_id')->constrained('modules')->onDelete('cascade');
                $table->timestamps();
                $table->unique(['methodology_id', 'module_id', 'depends_on_module_id'], 'module_dep_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('module_dependencies')) {
            Schema::dropIfExists('module_dependencies');
        }

        Schema::table('methodology_module', function (Blueprint $table) {
            if (Schema::hasColumn('methodology_module', 'report')) {
                $table->dropColumn('report');
            }
            if (Schema::hasColumn('methodology_module', 'minutes')) {
                $table->dropColumn('minutes');
            }
            if (Schema::hasColumn('methodology_module', 'weight')) {
                $table->dropColumn('weight');
            }
            if (Schema::hasColumn('methodology_module', 'number_of_questions')) {
                $table->dropColumn('number_of_questions');
            }
        });
    }
};


