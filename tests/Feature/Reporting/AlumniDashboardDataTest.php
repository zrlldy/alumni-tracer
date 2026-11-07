<?php

use App\Filament\Widgets\AlumniByProgramChart;
use App\Filament\Widgets\AlumniLineChart;
use App\Filament\Widgets\AlumniStats;
use App\Filament\Widgets\EmploymentStatus;
use App\Filament\Widgets\TracingProgress;
use App\Models\Alumni;
use App\Models\Campus;
use App\Models\Program;
use App\Models\User;
use App\Support\AlumniDashboardData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function createDashboardAnalyticsProgram(string $name): Program
{
    $campus = Campus::query()->create(['name' => $name.' Campus']);

    return Program::factory()->create([
        'campus_id' => $campus->id,
        'program_name' => $name,
    ]);
}

it('separates staff tracing dates from reported employment status', function () {
    $program = createDashboardAnalyticsProgram('Analytics Program');
    $user = User::factory()->create();

    Alumni::factory()->for($program)->for($user)->create([
        'employment_status' => 'employed',
        'date_traced' => null,
    ]);
    Alumni::factory()->for($program)->for($user)->create([
        'employment_status' => 'unemployed',
        'date_traced' => '2026-01-02',
    ]);
    Alumni::factory()->for($program)->for($user)->create([
        'employment_status' => 'untraced',
        'date_traced' => null,
    ]);
    Alumni::factory()->for($program)->for($user)->create([
        'employment_status' => null,
        'date_traced' => '2026-01-03',
    ]);
    Alumni::factory()->for($program)->for($user)->create([
        'employment_status' => 'employed',
        'date_traced' => '2026-01-04',
    ])->delete();

    $summary = app(AlumniDashboardData::class)->summary();

    expect($summary)->toMatchArray([
        'total' => 4,
        'statuses' => [
            'employed' => 1,
            'unemployed' => 1,
            'untraced' => 1,
            'unknown' => 1,
        ],
        'known_employment_total' => 2,
        'employment_rate' => 50.0,
        'traced' => 2,
        'not_traced' => 2,
        'tracing_coverage' => 50.0,
    ]);
});

it('returns unavailable rates when their denominators are empty', function () {
    $summary = app(AlumniDashboardData::class)->summary();
    $tracingWidget = new class extends TracingProgress
    {
        public function data(): array
        {
            return $this->getData();
        }
    };

    expect($summary['employment_rate'])->toBeNull()
        ->and($summary['tracing_coverage'])->toBeNull()
        ->and($tracingWidget->isEmpty())->toBeTrue();
});

it('keeps employment rate unavailable when every stored status is unknown', function () {
    $program = createDashboardAnalyticsProgram('Unknown Outcomes Program');
    $user = User::factory()->create();

    Alumni::factory()->for($program)->for($user)->create([
        'employment_status' => 'untraced',
        'date_traced' => null,
    ]);
    Alumni::factory()->for($program)->for($user)->create([
        'employment_status' => null,
        'date_traced' => '2026-01-03',
    ]);

    $summary = app(AlumniDashboardData::class)->summary();
    $statsWidget = new class extends AlumniStats
    {
        public function stats(): array
        {
            return $this->getStats();
        }
    };
    $stats = collect($statsWidget->stats())->keyBy(fn ($stat): string => $stat->getLabel());

    expect($summary['employment_rate'])->toBeNull()
        ->and($summary['tracing_coverage'])->toBe(50.0)
        ->and($stats['Employment Rate']->getValue())->toBe('Unavailable')
        ->and($stats['Employment Rate']->getDescription())->toBe('No alumni have reported being employed or unemployed');
});

it('shows employment and tracing denominators in their dashboard widgets', function () {
    $program = createDashboardAnalyticsProgram('Widget Program');
    $user = User::factory()->create();

    Alumni::factory()->for($program)->for($user)->create([
        'employment_status' => 'employed',
        'date_traced' => null,
    ]);
    Alumni::factory()->for($program)->for($user)->create([
        'employment_status' => null,
        'date_traced' => '2026-01-03',
    ]);

    $statsWidget = new class extends AlumniStats
    {
        public function stats(): array
        {
            return $this->getStats();
        }
    };
    $statusWidget = new class extends EmploymentStatus
    {
        public function data(): array
        {
            return $this->getData();
        }

        public function type(): string
        {
            return $this->getType();
        }
    };
    $tracingWidget = new class extends TracingProgress
    {
        public function data(): array
        {
            return $this->getData();
        }
    };

    $stats = collect($statsWidget->stats())->keyBy(fn ($stat): string => $stat->getLabel());

    expect($stats['Employment Rate']->getValue())->toBe('100.0%')
        ->and($stats['Employment Rate']->getDescription())->toBe('1 employed out of 1 alumni who reported employed or unemployed')
        ->and($stats['Alumni Traced']->getValue())->toBe('50.0%')
        ->and($stats['Alumni Traced']->getDescription())->toBe('1 of 2 alumni traced')
        ->and($stats['Missing or Unrecognized Status']->getValue())->toBe('1')
        ->and($statusWidget->data()['labels'])->toBe(['Employed (50.0%)', 'Unemployed (0.0%)', 'Untraced (0.0%)', 'Unknown (50.0%)'])
        ->and($statusWidget->data()['datasets'][0]['data'])->toBe([1, 0, 0, 1])
        ->and($statusWidget->type())->toBe('bar')
        ->and($tracingWidget->data()['datasets'][0]['data'])->toBe([1, 1])
        ->and($tracingWidget->data()['labels'])->toBe([
            'Traced: 1 of 2 alumni (50.0%)',
            'Not yet traced: 1 of 2 alumni (50.0%)',
        ]);
});

it('includes every graduation cohort in one grouped query', function () {
    $program = createDashboardAnalyticsProgram('Cohort Program');
    $user = User::factory()->create();

    Alumni::factory()->for($program)->for($user)->create([
        'graduation_year' => '2010-06-01',
        'employment_status' => 'employed',
    ]);
    Alumni::factory()->for($program)->for($user)->create([
        'graduation_year' => '2024-06-01',
        'employment_status' => null,
    ]);

    DB::connection()->flushQueryLog();
    DB::connection()->enableQueryLog();

    $cohorts = app(AlumniDashboardData::class)->employmentByGraduationYear();
    $queries = DB::connection()->getQueryLog();
    $chartWidget = new class extends AlumniLineChart
    {
        public function data(): array
        {
            return $this->getData();
        }

        public function maxHeight(): ?string
        {
            return $this->getMaxHeight();
        }
    };
    $chartData = $chartWidget->data();

    expect($cohorts['labels'])->toBe(['2010 · 1 counted in rate', '2024 · 0 counted in rate'])
        ->and($cohorts['rates'])->toBe([100.0, null])
        ->and($cohorts['known_totals'])->toBe([1, 0])
        ->and(count($queries))->toBe(1)
        ->and($chartData['labels'])->toBe(['2010 · 1 counted in rate', '2024 · 0 counted in rate'])
        ->and($chartData['datasets'][0]['data'])->toBe([100.0, null])
        ->and($chartWidget->maxHeight())->toBe('300px')
        ->and($chartWidget->getHeading())->toBe('Employment Rate by Graduation Year');
});

it('includes archived programs with alumni in the program chart data', function () {
    $program = createDashboardAnalyticsProgram('Archived Program');
    $user = User::factory()->create();
    $program->delete();

    Alumni::factory()->for($program)->for($user)->create([
        'employment_status' => 'unemployed',
    ]);
    Alumni::factory()->for($program)->for($user)->create([
        'employment_status' => null,
    ]);

    $programs = app(AlumniDashboardData::class)->employmentByProgram();

    expect($programs['labels'])->toBe(['Archived Program (archived) · 2 alumni'])
        ->and($programs['percentages']['unemployed'][0])->toBe(50.0)
        ->and($programs['percentages']['unknown'][0])->toBe(50.0)
        ->and(array_sum(array_column($programs['percentages'], 0)))->toBe(100.0);
});

it('matches program chart tooltips to the hovered horizontal row', function () {
    $chartWidget = new class extends AlumniByProgramChart
    {
        public function options(): array
        {
            return $this->getOptions();
        }
    };

    expect($chartWidget->options()['plugins']['tooltip'])
        ->toMatchArray(['mode' => 'index', 'intersect' => false, 'axis' => 'y']);
});
