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
        Schema::create('liability_module', function (Blueprint $table) {
            $table->id();
            $table->foreignId('liability_id')->constrained('liabilities')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('methodology_id')->constrained('methodology')->cascadeOnDelete();
            $table->foreignId('pillar_id')->nullable()->constrained('pillars')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['liability_id', 'module_id', 'methodology_id', 'pillar_id'], 'liability_module_unique');
            $table->index('liability_id');
            $table->index('module_id');
            $table->index('methodology_id');
            $table->index('pillar_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liability_module');
    }
};
