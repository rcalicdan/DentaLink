<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dental_services', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->decimal('price', 10, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('dental_services', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->decimal('price', 10, 2)->nullable(false)->change();
        });
    }
};