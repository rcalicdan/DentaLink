<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('patient_visit_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_visit_id')->constrained('patient_visits')->onDelete('cascade');
            $table->foreignId('dental_service_id')->constrained('dental_services');
            $table->decimal('service_price', 10, 2);
            $table->integer('quantity')->default(1);
            $table->text('service_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('patient_visit_services');
    }
};