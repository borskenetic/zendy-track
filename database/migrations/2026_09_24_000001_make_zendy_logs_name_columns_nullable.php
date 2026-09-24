<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('zendy_logs')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            if (Schema::hasColumn('zendy_logs', 'first_name')) {
                DB::statement('ALTER TABLE zendy_logs MODIFY first_name VARCHAR(100) NULL');
            }

            if (Schema::hasColumn('zendy_logs', 'last_name')) {
                DB::statement('ALTER TABLE zendy_logs MODIFY last_name VARCHAR(100) NULL');
            }
        }
    }

    public function down(): void
    {
        // Intentionally left empty: forcing NOT NULL would fail on existing null rows.
    }
};
