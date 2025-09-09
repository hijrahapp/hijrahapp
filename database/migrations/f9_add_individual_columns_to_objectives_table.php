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
        Schema::table('objectives', function (Blueprint $table) {
            // Shared columns for multiple types
            $table->text('header')->nullable()->after('order');
            $table->text('content')->nullable()->after('header');
            $table->text('description')->nullable()->after('content');

            // URL fields for video, audio, book types
            $table->string('content_url')->nullable()->after('description');
            $table->string('content_image')->nullable()->after('content_url');

            // List fields (JSON arrays)
            $table->json('advices')->nullable()->after('content_image');
            $table->json('challenges')->nullable()->after('advices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('objectives', function (Blueprint $table) {
            $table->dropColumn([
                'header',
                'content',
                'description',
                'content_url',
                'content_image',
                'advices',
                'challenges',
            ]);
        });
    }
};
