<?php

use App\Models\Alumni;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates alumni with linked users and programs on a campus', function () {
    $alumni = Alumni::factory()->create();

    $this->assertModelExists($alumni->user);
    $this->assertModelExists($alumni->program);
    $this->assertModelExists($alumni->program->campus);
});
