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
        Schema::create('campus_user_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_allocation_id')->constrained('user_allocations')->cascadeOnDelete();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['user_allocation_id', 'campus_id'],
                'campus_allocation_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campus_user_allocations');
    }
};
