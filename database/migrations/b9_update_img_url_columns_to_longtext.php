<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE pillars MODIFY img_url LONGTEXT NULL');
            DB::statement('ALTER TABLE modules MODIFY img_url LONGTEXT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE pillars ALTER COLUMN img_url TYPE TEXT');
            DB::statement('ALTER TABLE modules ALTER COLUMN img_url TYPE TEXT');
        } else {
            // For sqlite or other drivers, keep as-is to avoid breaking local test envs
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE pillars MODIFY img_url VARCHAR(255) NULL');
            DB::statement('ALTER TABLE modules MODIFY img_url VARCHAR(255) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE pillars ALTER COLUMN img_url TYPE VARCHAR(255)');
            DB::statement('ALTER TABLE modules ALTER COLUMN img_url TYPE VARCHAR(255)');
        } else {
            // No-op for other drivers
        }
    }
};
