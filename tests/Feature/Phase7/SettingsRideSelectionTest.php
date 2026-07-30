<?php

use App\Models\Ride;
use App\Models\UserSetting;
use Livewire\Livewire;

it('loads ride_list from the saved ride_short_name', function () {
    Ride::query()->create([
        'ride' => '100K',
        'ride_desc' => '100K Ride',
        'distance_k' => 100,
        'distance_miles' => 62.1,
    ]);

    UserSetting::set('ride_short_name', '100k');

    Livewire::test('settings-page')
        ->assertSet('ride_list', '100k');
});

it('updates ride_short_name when ride_list changes', function () {
    $ride = Ride::query()->create([
        'ride' => '110K',
        'ride_desc' => '110K Ride',
        'distance_k' => 110,
        'distance_miles' => 68.4,
    ]);

    Livewire::test('settings-page')
        ->set('ride_list', '110k');

    expect(UserSetting::get('ride_short_name'))->toBe('110k')
        ->and(UserSetting::get('ride_id'))->toBe((string) $ride->id)
        ->and(UserSetting::get('ride_desc'))->toBe('110K Ride');
});
