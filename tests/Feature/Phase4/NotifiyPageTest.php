<?php

use App\Models\UserSetting;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('redirects guest users away from notifiy page', function () {
    UserSetting::set('guest_user', 'true');

    session([
        'auth_token' => 'valid-token',
        'token_verified_at' => now(),
    ]);

    get(route('notifiy'))
        ->assertRedirectToRoute('home');
});

it('allows non-guest users to access notifiy page', function () {
    UserSetting::set('guest_user', 'false');

    session([
        'auth_token' => 'valid-token',
        'token_verified_at' => now(),
    ]);

    get(route('notifiy'))
        ->assertOk()
        ->assertSee('Notify Ride Director');
});

it('sends ride director message from notifiy page', function () {
    Http::fake([
        '*/ride-director/messages' => Http::response(['status' => 'ok'], 200),
    ]);

    UserSetting::set('guest_user', 'false');
    UserSetting::set('first_name', 'Chris');
    UserSetting::set('last_name', 'Rider');
    UserSetting::set('bib', '519');
    UserSetting::set('ride_short_name', '100k');
    UserSetting::set('push_token', 'native-token-123');

    Livewire::test('notifiy')
        ->set('message', 'I need support near the aid station.')
        ->call('send')
        ->assertHasNoErrors()
        ->assertSet('message', '')
        ->assertSee('Message sent to the Ride Director.');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/ride-director/messages')
        && filled($request['date_sent'])
        && $request['message'] === 'I need support near the aid station.'
        && $request['first_name'] === 'Chris'
        && $request['last_name'] === 'Rider'
        && $request['bib'] === '519'
        && $request['ride_short_name'] === '100k'
        && $request['push_token'] === 'native-token-123'
    );
});

it('loads saved push token on notifiy page', function () {
    UserSetting::set('guest_user', 'false');
    UserSetting::set('push_token', 'saved-token-abc');

    Livewire::test('notifiy')
        ->assertSet('pushToken', 'saved-token-abc');
});
