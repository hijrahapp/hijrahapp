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
        Schema::create('user_context_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('context_type');
            $table->unsignedBigInteger('context_id');
            $table->unsignedBigInteger('methodology_id')->default(0); // 0 when not applicable
            $table->unsignedBigInteger('pillar_id')->default(0); // 0 when not applicable
            $table->enum('status', ['in_progress', 'completed'])->default('in_progress');
            $table->timestamps();

            $table->index(['user_id', 'context_type']);
            $table->unique(['user_id', 'context_type', 'context_id', 'methodology_id', 'pillar_id'], 'ucs_unique_scope');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_context_statuses');
    }
};
