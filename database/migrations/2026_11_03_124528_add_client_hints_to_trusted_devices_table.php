<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trusted_devices', function (Blueprint $table) {
            $table->string('device_model')->nullable()->after('user_agent');
            $table->string('device_platform')->nullable()->after('device_model');
            $table->string('device_platform_version')->nullable()->after('device_platform');
        });
    }

    public function down(): void
    {
        Schema::table('trusted_devices', function (Blueprint $table) {
            $table->dropColumn(['device_model', 'device_platform', 'device_platform_version']);
        });
    }
};