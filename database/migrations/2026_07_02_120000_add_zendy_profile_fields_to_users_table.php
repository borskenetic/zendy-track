<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'course')) {
                $table->string('course')->nullable();
            }

            if (! Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable();
            }

            if (! Schema::hasColumn('users', 'campus')) {
                $table->string('campus')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('users', 'course') ? 'course' : null,
                Schema::hasColumn('users', 'department') ? 'department' : null,
                Schema::hasColumn('users', 'campus') ? 'campus' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
