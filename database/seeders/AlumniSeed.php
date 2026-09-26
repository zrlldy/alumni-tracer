<?php

namespace Database\Seeders;

use App\Models\Alumni;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AlumniSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Alumni::factory()->create();
    }
}
