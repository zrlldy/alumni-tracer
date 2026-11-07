<?php

namespace Database\Factories;

use App\Models\Campus;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campus_id' => Campus::factory(),
            'program_name' => fake()->name(),
            'is_active' => fake()->boolean(),
        ];
    }
}
