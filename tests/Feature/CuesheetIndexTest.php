<?php

use App\Models\Cuesheet;
use App\Models\UserSetting;
use Livewire\Livewire;

it('loads cuesheets for the saved ride name regardless of casing', function () {
    UserSetting::set('ride_short_name', '100K');

    $completedCuesheet = Cuesheet::query()->create([
        'ride' => '100k',
        'turn' => 'L',
        'notes' => 'Left on Main',
        'distance' => 12.5,
        'completed' => 1,
    ]);

    $pendingCuesheet = Cuesheet::query()->create([
        'ride' => '100k',
        'turn' => 'R',
        'notes' => 'Right on Elm',
        'distance' => 15.2,
        'completed' => 0,
    ]);

    Cuesheet::query()->create([
        'ride' => '140k',
        'turn' => 'S',
        'notes' => 'Continue straight',
        'distance' => 20.0,
        'completed' => 1,
    ]);

    Livewire::test('cuesheet-index')
        ->assertSet('rideShortName', '100k')
        ->assertSet('updateRideIds', [$completedCuesheet->id])
        ->assertSee('Left on Main')
        ->assertSee('Right on Elm')
        ->assertDontSee('Continue straight');

    expect(
        Livewire::test('cuesheet-index')->instance()->cuesheets()->pluck('id')->all()
    )->toBe([$completedCuesheet->id, $pendingCuesheet->id]);
});
