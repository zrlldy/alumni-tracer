<?php

use App\Filament\Imports\AlumniImporter;
use App\Models\Campus;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves the department within its selected campus', function () {
    $campus = Campus::factory()->create(['name' => 'North Campus']);
    $program = Program::factory()->for($campus)->create(['program_name' => 'Nursing', 'is_active' => true]);

    $row = (new AlumniImporter)->prepareForValidation([
        'campus' => ' North Campus ',
        'department' => 'Nursing',
    ], 2);

    expect($row['program_id'])->toBe($program->id);
});

it('does not resolve a department from a different campus', function () {
    $campus = Campus::factory()->create(['name' => 'North Campus']);
    Program::factory()->for($campus)->create(['program_name' => 'Nursing', 'is_active' => true]);

    $row = (new AlumniImporter)->prepareForValidation([
        'campus' => 'South Campus',
        'department' => 'Nursing',
    ], 2);

    expect($row['program_id'])->toBeNull();
});

it('does not choose between duplicate campus and department matches', function () {
    $firstCampus = Campus::factory()->create(['name' => 'North Campus']);
    $secondCampus = Campus::factory()->create(['name' => 'North Campus']);
    Program::factory()->for($firstCampus)->create(['program_name' => 'Nursing', 'is_active' => true]);
    Program::factory()->for($secondCampus)->create(['program_name' => 'Nursing', 'is_active' => true]);

    $row = (new AlumniImporter)->prepareForValidation([
        'campus' => 'North Campus',
        'department' => 'Nursing',
    ], 2);

    expect($row['program_id'])->toBeNull();
});
