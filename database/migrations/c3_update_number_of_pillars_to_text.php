<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE methodology MODIFY number_of_pillars TEXT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE methodology ALTER COLUMN number_of_pillars TYPE TEXT USING number_of_pillars::text');
        } else {
            // sqlite and others: skip to avoid breaking local/in-memory databases
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE methodology MODIFY number_of_pillars INT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE methodology ALTER COLUMN number_of_pillars TYPE INTEGER USING NULLIF(number_of_pillars, \'\')::integer');
        } else {
            // No-op for other drivers
        }
    }
};


