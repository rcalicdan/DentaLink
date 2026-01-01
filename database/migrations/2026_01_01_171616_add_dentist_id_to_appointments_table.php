<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('dentist_id')->nullable()->after('branch_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['dentist_id']);
            $table->dropColumn('dentist_id');
        });
    }
};