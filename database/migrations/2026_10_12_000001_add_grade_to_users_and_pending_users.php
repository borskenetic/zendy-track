<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'grade')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('grade')->nullable()->after('course');
            });
        }

        if (Schema::hasTable('pending_users') && ! Schema::hasColumn('pending_users', 'grade')) {
            Schema::table('pending_users', function (Blueprint $table) {
                $table->string('grade')->nullable()->after('course');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'grade')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('grade');
            });
        }

        if (Schema::hasTable('pending_users') && Schema::hasColumn('pending_users', 'grade')) {
            Schema::table('pending_users', function (Blueprint $table) {
                $table->dropColumn('grade');
            });
        }
    }
};
