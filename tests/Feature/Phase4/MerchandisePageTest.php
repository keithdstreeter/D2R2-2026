<?php

use Livewire\Livewire;
use Native\Mobile\Facades\Browser;

use function Pest\Laravel\get;

it('renders the merchandise page for authenticated users', function () {
    session([
        'auth_token' => 'valid-token',
        'token_verified_at' => now(),
    ]);

    get(route('merchandise'))
        ->assertOk()
        ->assertSee('Merchandise')
        ->assertSee('Open Merchandise Store');
});

it('opens the merchandise shop in the native in-app browser', function () {
    Browser::shouldReceive('inApp')
        ->once()
        ->with('https://d2r2.franklinlandtrust.org/shop/')
        ->andReturn(true);

    Livewire::test('merchandise')
        ->call('openShop');
});
