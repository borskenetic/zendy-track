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
        Schema::table('fine_settings', function (Blueprint $table) {
            $table->integer('loan_duration_days')->default(7)->after('grace_period_days');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fine_settings', function (Blueprint $table) {
            //
        });
    }
};
