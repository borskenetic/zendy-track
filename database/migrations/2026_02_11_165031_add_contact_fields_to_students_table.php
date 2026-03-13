<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {

            $table->string('mobile_number')->nullable()->after('year');
            $table->text('address')->nullable()->after('mobile_number');

            $table->string('emergency_person')->nullable()->after('address');
            $table->string('emergency_relationship')->nullable()->after('emergency_person');
            $table->string('emergency_number')->nullable()->after('emergency_relationship');
            $table->text('emergency_address')->nullable()->after('emergency_number');

        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {

            $table->dropColumn([
                'mobile_number',
                'address',
                'emergency_person',
                'emergency_relationship',
                'emergency_number',
                'emergency_address'
            ]);

        });
    }
};

