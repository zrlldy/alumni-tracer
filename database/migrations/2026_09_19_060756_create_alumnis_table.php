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
        Schema::create('alumnis', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('student_number');
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone_number');
            $table->string('current_address');
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->string('graduation_year');
            $table->enum('employment_status', ['unemployed,employed', 'untraced'])->nullable();
            $table->string('remarks')->nullable();
            $table->date('date_traced')->nullable();
            $table->string('trace_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnis');
    }
};
