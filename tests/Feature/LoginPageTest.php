<?php

use App\Models\Registration;
use App\Models\UserSetting;
use App\Services\ContentSync;
use App\Services\DeviceIdentity;
use App\Services\PushNotificationManager;
use Livewire\Livewire;

it('matches registrations with normalized names and selected date of birth', function () {
    $this->mock(PushNotificationManager::class)
        ->shouldReceive('initialize')
        ->once()
        ->andReturnNull();

    $this->mock(ContentSync::class)
        ->shouldReceive('sync')
        ->once()
        ->andReturn(0);

    $this->mock(DeviceIdentity::class)
        ->shouldReceive('getDeviceInfo')
        ->never();

    Registration::query()->create([
        'bib' => '1',
        'first_name' => 'Christopher',
        'last_name' => 'Capeliini',
        'phone' => '555-555-5555',
        'category_entered' => '100K',
        'email' => 'chris@example.com',
        'dob' => '03/07/1977',
        'gender' => 'M',
    ]);

    Livewire::test('login-page')
        ->set('first_name', '  christopher  ')
        ->set('last_name', 'CAPELIINI')
        ->set('dob_month', '3')
        ->set('dob_day', '7')
        ->set('dob_year', '1977')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('home'));

    expect(UserSetting::get('Guest_User'))->toBe('false')
        ->and(UserSetting::get('first_name'))->toBe('Christopher')
        ->and(UserSetting::get('last_name'))->toBe('Capeliini')
        ->and(UserSetting::get('ride_short_name'))->toBe('100k')
        ->and(session('auth_token'))->not->toBeNull();
});

it('shows a validation-style error when login fields are incomplete', function () {
    $this->mock(PushNotificationManager::class)
        ->shouldReceive('initialize')
        ->once()
        ->andReturnNull();

    $this->mock(ContentSync::class)
        ->shouldReceive('sync')
        ->once()
        ->andReturn(0);

    Livewire::test('login-page')
        ->set('first_name', '')
        ->set('last_name', '')
        ->set('dob_month', '')
        ->set('dob_day', '')
        ->set('dob_year', '')
        ->set('dob_full', '')
        ->call('login')
        ->assertSet('error', 'Please enter your first name, last name, and date of birth.');
});
