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
        Schema::create('feedback_forms', function (Blueprint $table) {
            $table->id();
            $table->string('version'); // e.g., "1.0", "1.1", "2.0"
            $table->string('name'); // e.g., "Program Completion Feedback"
            $table->json('form_structure'); // Complete form definition
            $table->string('language')->default('ar'); // ar, en
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['language']);
            $table->unique(['version', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_forms');
    }
};
