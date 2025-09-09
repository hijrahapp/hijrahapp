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
        Schema::table('steps', function (Blueprint $table) {
            // Add user_id to track which user this step belongs to (nullable for template steps)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade')->after('program_id');

            // Progress tracking fields
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started')->after('challenges');
            $table->text('thought')->nullable()->after('status'); // For journal steps
            $table->integer('score')->nullable()->after('thought'); // For quiz steps
            $table->integer('challenges_done')->nullable()->after('score'); // For challenge steps
            $table->decimal('percentage', 5, 2)->nullable()->after('challenges_done'); // For challenge steps
            $table->timestamp('started_at')->nullable()->after('percentage');
            $table->timestamp('completed_at')->nullable()->after('started_at');

            // Add index for better performance
            $table->index(['user_id', 'program_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('steps', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'status',
                'thought',
                'score',
                'challenges_done',
                'percentage',
                'started_at',
                'completed_at',
            ]);
            $table->dropIndex(['user_id', 'program_id', 'status']);
        });
    }
};
