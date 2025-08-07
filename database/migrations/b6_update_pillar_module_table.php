<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the existing pillar_module table
        Schema::dropIfExists('pillar_module');

        // Recreate pillar_module table with methodology_id
        Schema::create('pillar_module', function (Blueprint $table) {
            $table->id();
            $table->foreignId('methodology_id')->constrained('methodology')->onDelete('cascade');
            $table->foreignId('pillar_id')->constrained('pillars')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['methodology_id', 'pillar_id', 'module_id'], 'pillar_module_unique');
        });
    }

    public function down(): void
    {
        // Drop the updated pillar_module table
        Schema::dropIfExists('pillar_module');

        // Recreate the original pillar_module table without methodology_id
        Schema::create('pillar_module', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pillar_id')->constrained('pillars')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['pillar_id', 'module_id']);
        });
    }
}; 