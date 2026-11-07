<?php

use App\Filament\Widgets\AlumniLineChart;
use App\Support\AlumniDashboardData;
use Database\Seeders\DashboardDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates synthetic dashboard data across statuses, programs, cohorts and tracing', function () {
    $this->seed(DashboardDemoSeeder::class);

    $summary = app(AlumniDashboardData::class)->summary();
    $programs = app(AlumniDashboardData::class)->employmentByProgram();
    $cohorts = app(AlumniDashboardData::class)->employmentByGraduationYear();
    $chart = new class extends AlumniLineChart
    {
        public function data(): array
        {
            return $this->getData();
        }

        public function isChartEmpty(): bool
        {
            return $this->isEmpty();
        }
    };
    $chartData = $chart->data();

    expect($summary)->toMatchArray([
        'total' => 32,
        'statuses' => [
            'employed' => 10,
            'unemployed' => 11,
            'untraced' => 6,
            'unknown' => 5,
        ],
        'known_employment_total' => 21,
        'employment_rate' => 47.6,
        'traced' => 16,
        'tracing_coverage' => 50.0,
    ])
        ->and($programs['labels'])->toHaveCount(4)
        ->and($cohorts['labels'])->toHaveCount(4)
        ->and($chart->isChartEmpty())->toBeFalse()
        ->and($chartData['datasets'][0]['data'])->toBe([57.1, 42.9, 50.0, 33.3])
        ->and(array_sum(array_column($programs['percentages'], 0)))->toBe(100.0);

    $this->assertDatabaseCount('alumnis', 32);
    $this->assertDatabaseCount('programs', 4);
    $this->assertDatabaseCount('campuses', 4);
});
