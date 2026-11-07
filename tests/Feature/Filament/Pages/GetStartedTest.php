<?php

use App\Filament\Pages\GetStarted;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Filament::setCurrentPanel('online');
});

it('guides signed-in accounts without an allocation to contact the school office', function () {
    $user = User::factory()->create(['user_allocation_id' => null]);

    Livewire::actingAs($user)
        ->test(GetStarted::class)
        ->assertSee('Your account is ready, but alumni portal access has not been assigned yet.')
        ->assertSee('contact your school office')
        ->assertSee('Once access is assigned, sign in again');
});
