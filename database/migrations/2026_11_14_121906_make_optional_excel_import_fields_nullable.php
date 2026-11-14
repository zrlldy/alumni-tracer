<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const NULLABLE_COLUMNS = [
        'alumnis' => ['middle_name', 'email', 'phone_number', 'current_address'],
        'alumni_education' => ['program', 'degree_level', 'units_completed', 'status', 'started_at', 'ended_at'],
        'alumni_employments' => ['company_address', 'employment_type', 'supported_documents', 'is_current'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alumnis', function (Blueprint $table): void {
            $table->string('middle_name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('phone_number')->nullable()->change();
            $table->string('current_address')->nullable()->change();
        });

        Schema::table('alumni_education', function (Blueprint $table): void {
            $table->string('program')->nullable()->change();
            $table->string('degree_level')->nullable()->change();
            $table->unsignedInteger('units_completed')->nullable()->change();
            $table->string('status')->nullable()->change();
            $table->date('started_at')->nullable()->change();
            $table->date('ended_at')->nullable()->change();
        });

        Schema::table('alumni_employments', function (Blueprint $table): void {
            $table->string('company_address')->nullable()->change();
            $table->string('employment_type')->nullable()->change();
            $table->string('supported_documents')->nullable()->change();
            $table->boolean('is_current')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::NULLABLE_COLUMNS as $table => $columns) {
            $containsNull = DB::table($table)
                ->where(function (Builder $query) use ($columns): void {
                    foreach ($columns as $column) {
                        $query->orWhereNull($column);
                    }
                })
                ->exists();

            if ($containsNull) {
                throw new RuntimeException("Cannot restore required import fields while {$table} contains NULL values.");
            }
        }

        Schema::table('alumnis', function (Blueprint $table): void {
            $table->string('middle_name')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('phone_number')->nullable(false)->change();
            $table->string('current_address')->nullable(false)->change();
        });

        Schema::table('alumni_education', function (Blueprint $table): void {
            $table->string('program')->nullable(false)->change();
            $table->string('degree_level')->nullable(false)->change();
            $table->unsignedInteger('units_completed')->nullable(false)->change();
            $table->string('status')->nullable(false)->change();
            $table->date('started_at')->nullable(false)->change();
            $table->date('ended_at')->nullable(false)->change();
        });

        Schema::table('alumni_employments', function (Blueprint $table): void {
            $table->string('company_address')->nullable(false)->change();
            $table->string('employment_type')->nullable(false)->change();
            $table->string('supported_documents')->nullable(false)->change();
            $table->boolean('is_current')->nullable(false)->change();
        });
    }
};
