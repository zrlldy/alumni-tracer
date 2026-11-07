<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alumnis', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('alumnis')->whereNull('user_id')->exists()) {
            throw new RuntimeException('Cannot restore alumnis.user_id to NOT NULL while unlinked alumni records exist.');
        }

        Schema::table('alumnis', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
