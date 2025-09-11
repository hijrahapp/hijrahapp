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
        Schema::create('user_liability_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('liability_id')->constrained('liabilities')->cascadeOnDelete();
            $table->json('completed_todos')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'liability_id']);
            $table->index('user_id');
            $table->index('liability_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_liability_progress');
    }
};
