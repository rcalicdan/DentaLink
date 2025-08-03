<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('phone', 20);
            $table->string('email', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('registration_branch_id')->constrained('branches', 'id');
              $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('patients');
    }
};
