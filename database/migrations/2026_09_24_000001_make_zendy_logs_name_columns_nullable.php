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

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $nullableStringColumns = [
            'first_name' => 100,
            'last_name' => 100,
            'email' => 255,
            'course' => 150,
            'department' => 150,
            'campus' => 150,
        ];

        foreach ($nullableStringColumns as $column => $length) {
            if (! Schema::hasColumn('zendy_logs', $column)) {
                continue;
            }

            DB::statement("ALTER TABLE zendy_logs MODIFY {$column} VARCHAR({$length}) NULL");
        }
    }

    public function down(): void
    {
        // Intentionally left empty: forcing NOT NULL would fail on existing null/empty rows.
    }
};
