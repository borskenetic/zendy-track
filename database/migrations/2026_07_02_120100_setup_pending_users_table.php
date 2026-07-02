<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pending_users')) {
            Schema::create('pending_users', function (Blueprint $table) {
                $table->id();
                $table->string('fname');
                $table->string('lname');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('course')->nullable();
                $table->string('department')->nullable();
                $table->string('campus')->nullable();
                $table->string('role')->default('student');
                $table->timestamps();
            });

            return;
        }

        Schema::table('pending_users', function (Blueprint $table) {
            if (! Schema::hasColumn('pending_users', 'course')) {
                $table->string('course')->nullable();
            }

            if (! Schema::hasColumn('pending_users', 'department')) {
                $table->string('department')->nullable();
            }

            if (! Schema::hasColumn('pending_users', 'campus')) {
                $table->string('campus')->nullable();
            }

            if (! Schema::hasColumn('pending_users', 'role')) {
                $table->string('role')->default('student');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_users');
    }
};
