<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_step_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('step_id');
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'skipped'])
                ->default('not_started');
            $table->text('thought')->nullable();
            $table->integer('score')->nullable();
            $table->integer('challenges_done')->default(0);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'program_id']);
            $table->index(['step_id', 'status']);
            $table->unique(['user_id', 'program_id', 'step_id'], 'user_program_step_unique');

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');
            $table->foreign('step_id')->references('id')->on('steps')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_step_progress');
    }
};
