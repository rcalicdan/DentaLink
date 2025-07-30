<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('patient_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments');
            $table->datetime('visit_date');
            $table->text('notes')->nullable();
            $table->decimal('total_amount_paid', 10, 2);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('patient_visits');
    }
};