<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('book_logs', function ($table) {
            $table->decimal('fine_incurred', 8, 2)->nullable()->after('returned_date');
        });
    }
    
    public function down()
    {
        Schema::table('book_logs', function ($table) {
            $table->dropColumn('fine_incurred');
        });
    }

};
