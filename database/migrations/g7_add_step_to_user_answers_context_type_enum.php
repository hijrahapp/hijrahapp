<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // For MySQL, we need to modify the enum to add 'step'
            DB::statement("ALTER TABLE user_answers MODIFY COLUMN context_type ENUM('methodology', 'pillar', 'module', 'step') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Remove 'step' from the enum
            DB::statement("ALTER TABLE user_answers MODIFY COLUMN context_type ENUM('methodology', 'pillar', 'module') NOT NULL");
        }
        // For SQLite, no rollback needed since we didn't modify the schema
    }
};
