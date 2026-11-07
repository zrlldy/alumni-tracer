<?php

namespace App\Support;

use App\Models\Alumni;
use App\Models\Program;

class AlumniDashboardData
{
    /**
     * @var list<string>
     */
    private const STATUSES = ['employed', 'unemployed', 'untraced', 'unknown'];

    /**
     * @return array{
     *     total: int,
     *     statuses: array{employed: int, unemployed: int, untraced: int, unknown: int},
     *     known_employment_total: int,
     *     employment_rate: ?float,
     *     traced: int,
     *     not_traced: int,
     *     tracing_coverage: ?float
     * }
     */
    public function summary(): array
    {
        $statuses = array_fill_keys(self::STATUSES, 0);
        $total = 0;
        $traced = 0;

        $rows = Alumni::query()
            ->select('employment_status')
            ->selectRaw('CASE WHEN date_traced IS NULL THEN 0 ELSE 1 END AS is_traced, COUNT(*) AS total')
            ->groupBy('employment_status')
            ->groupByRaw('CASE WHEN date_traced IS NULL THEN 0 ELSE 1 END')
            ->get();

        foreach ($rows as $row) {
            $status = match ($row->employment_status) {
                'employed', 'unemployed', 'untraced' => $row->employment_status,
                default => 'unknown',
            };
            $count = (int) $row->total;

            $statuses[$status] += $count;
            $total += $count;

            if ((int) $row->is_traced === 1) {
                $traced += $count;
            }
        }

        $knownEmploymentTotal = $statuses['employed'] + $statuses['unemployed'];

        return [
            'total' => $total,
            'statuses' => $statuses,
            'known_employment_total' => $knownEmploymentTotal,
            'employment_rate' => $knownEmploymentTotal > 0
                ? round(($statuses['employed'] / $knownEmploymentTotal) * 100, 1)
                : null,
            'traced' => $traced,
            'not_traced' => $total - $traced,
            'tracing_coverage' => $total > 0
                ? round(($traced / $total) * 100, 1)
                : null,
        ];
    }

    /**
     * @return array{
     *     labels: list<string>,
     *     rates: list<?float>,
     *     known_totals: list<int>
     * }
     */
    public function employmentByGraduationYear(): array
    {
        $yearExpression = 'SUBSTR(graduation_year, 1, 4)';
        $rows = Alumni::query()
            ->selectRaw("{$yearExpression} AS year, employment_status, COUNT(*) AS total")
            ->groupByRaw("{$yearExpression}, employment_status")
            ->orderBy('year')
            ->get();

        $cohorts = [];

        foreach ($rows as $row) {
            $year = $row->year === null ? null : (string) $row->year;
            $key = $year ?? 'unknown';
            $status = match ($row->employment_status) {
                'employed', 'unemployed', 'untraced' => $row->employment_status,
                default => 'unknown',
            };

            $cohorts[$key] ??= [
                'label' => $year ?? 'Unknown cohort',
                'statuses' => array_fill_keys(self::STATUSES, 0),
            ];
            $cohorts[$key]['statuses'][$status] += (int) $row->total;
        }

        $cohorts = array_values($cohorts);
        $knownTotals = array_map(
            fn (array $cohort): int => $cohort['statuses']['employed'] + $cohort['statuses']['unemployed'],
            $cohorts,
        );
        $rates = array_map(
            fn (array $cohort, int $knownTotal): ?float => $knownTotal > 0
                ? round(($cohort['statuses']['employed'] / $knownTotal) * 100, 1)
                : null,
            $cohorts,
            $knownTotals,
        );
        $labels = array_map(
            fn (array $cohort, int $knownTotal): string => $cohort['label'].' · '.$knownTotal.' counted in rate',
            $cohorts,
            $knownTotals,
        );

        return [
            'labels' => $labels,
            'rates' => $rates,
            'known_totals' => $knownTotals,
        ];
    }

    /**
     * @return array{
     *     labels: list<string>,
     *     percentages: array{employed: list<float>, unemployed: list<float>, untraced: list<float>, unknown: list<float>}
     * }
     */
    public function employmentByProgram(): array
    {
        $programs = Program::withTrashed()
            ->whereHas('alumni')
            ->orderBy('program_name')
            ->get(['id', 'program_name', 'deleted_at']);

        $countsByProgram = Alumni::query()
            ->selectRaw('program_id, employment_status, COUNT(*) AS total')
            ->groupBy('program_id', 'employment_status')
            ->get()
            ->groupBy('program_id');

        $labels = [];
        $statuses = array_fill_keys(self::STATUSES, []);

        foreach ($programs as $program) {
            $programLabel = $program->program_name.($program->trashed() ? ' (archived)' : '');
            $programStatuses = array_fill_keys(self::STATUSES, 0);

            foreach ($countsByProgram->get($program->id, collect()) as $row) {
                $status = match ($row->employment_status) {
                    'employed', 'unemployed', 'untraced' => $row->employment_status,
                    default => 'unknown',
                };
                $programStatuses[$status] += (int) $row->total;
            }

            $programTotal = array_sum($programStatuses);
            $labels[] = $programLabel.' · '.$programTotal.' alumni';

            foreach (self::STATUSES as $status) {
                $statuses[$status][] = $programTotal > 0
                    ? ($programStatuses[$status] / $programTotal) * 100
                    : 0.0;
            }
        }

        return [
            'labels' => $labels,
            'percentages' => $statuses,
        ];
    }
}
