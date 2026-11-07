<?php

namespace Database\Factories;

use App\Models\Alumni;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alumni>
 */
class AlumniFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $graduationDate = fake()->dateTimeBetween('2023-01-01', '-1 day');

        return [
            'student_number' => fake()->unique()->numberBetween(100, 999999),
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->lastName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->phoneNumber(),
            'current_address' => fake()->address(),
            'program_id' => Program::factory(),
            'graduation_year' => $graduationDate,
            'employment_status' => fake()->randomElement([
                'unemployed',
                'employed',
                'untraced',
                null,
            ]),
            'user_id' => User::factory(),
            'remarks' => fake()->sentence(),
            'date_traced' => fake()->dateTimeBetween($graduationDate, 'now'),
            'trace_by' => fake()->name(),
        ];
    }
}
