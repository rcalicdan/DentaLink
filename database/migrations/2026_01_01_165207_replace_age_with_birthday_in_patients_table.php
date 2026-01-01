<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->date('birthday')->nullable()->after('email');
            $table->dropColumn('age');
        });
    }

    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable()->after('email');
            $table->dropColumn('birthday');
        });
    }
};