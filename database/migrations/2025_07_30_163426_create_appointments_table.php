<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->date('appointment_date');
            $table->integer('queue_number')->nullable();
            $table->enum('status', ['Scheduled', 'In Progress', 'Completed', 'Cancelled', 'No Show'])->default('Scheduled');
            $table->text('notes')->nullable();
            $table->boolean('has_visit')->default(false);
            $table->foreignId('created_by')->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
