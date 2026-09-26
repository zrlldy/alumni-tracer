<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Program::factory(10)->create();
    }
}
