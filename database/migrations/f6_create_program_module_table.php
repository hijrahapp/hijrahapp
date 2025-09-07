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
        Schema::create('program_module', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->foreignId('methodology_id')->constrained('methodology')->onDelete('cascade');
            $table->foreignId('pillar_id')->nullable()->constrained('pillars')->onDelete('cascade');
            $table->decimal('min_score', 5, 2)->default(0.00);
            $table->decimal('max_score', 5, 2)->default(100.00);
            $table->timestamps();
            $table->unique(['program_id', 'module_id', 'methodology_id', 'pillar_id'], 'program_module_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_module');
    }
};
