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
        Schema::create('user_enrichments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('enrichment_id')->constrained()->onDelete('cascade');
            $table->boolean('like')->default(false);
            $table->boolean('favorite')->default(false);
            $table->timestamps();

            // Ensure unique user-enrichment combination
            $table->unique(['user_id', 'enrichment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_enrichments');
    }
};
