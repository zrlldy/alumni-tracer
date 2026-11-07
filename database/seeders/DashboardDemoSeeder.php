<?php

namespace Database\Seeders;

use App\Models\Alumni;
use App\Models\Program;
use Illuminate\Database\Seeder;

class DashboardDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            'Demo Program Alpha' => ['employed', 'employed', 'employed', 'employed', 'unemployed', 'unemployed', 'untraced', null],
            'Demo Program Beta' => ['employed', 'employed', 'unemployed', 'unemployed', 'unemployed', 'unemployed', 'untraced', null],
            'Demo Program Gamma' => ['employed', 'employed', 'employed', 'unemployed', 'unemployed', 'unemployed', 'untraced', null],
            'Demo Program Delta' => ['employed', 'unemployed', 'unemployed', 'untraced', 'untraced', 'untraced', null, null],
        ];
        $graduationYears = [2012, 2015, 2018, 2020];

        foreach ($programs as $programName => $statuses) {
            $program = Program::factory()->create([
                'program_name' => $programName,
                'is_active' => true,
            ]);

            foreach ($statuses as $index => $status) {
                $isTraced = $index % 2 === 0;
                $graduationYear = $graduationYears[$index % count($graduationYears)];

                Alumni::factory()->for($program)->create([
                    'employment_status' => $status,
                    'graduation_year' => $graduationYear.'-06-15',
                    'date_traced' => $isTraced
                        ? fake()->dateTimeBetween(($graduationYear + 1).'-01-01', 'now')->format('Y-m-d')
                        : null,
                    'trace_by' => $isTraced ? fake()->name() : null,
                ]);
            }
        }
    }
}
