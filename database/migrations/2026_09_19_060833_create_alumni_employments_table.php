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
        Schema::create('alumni_employments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->constrained('alumnis')->cascadeOnDelete();
            $table->string('company_name');
            $table->string('position')->nullable();
            $table->string('company_address');
            $table->string('industry')->nullable();
            $table->string('employment_type');
            $table->boolean('is_course_related')->nullable();
            $table->date('date_hired');
            $table->date('starting_date')->nullable();
            $table->date('ended_at')->nullable();
            $table->string('supported_documents');
            $table->boolean('is_current');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_employments');
    }
};
