<?php

use App\Models\AgeGroup;
use App\Models\UserSetting;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders the HomePage component', function () {
    get('/')->assertSuccessful();
});

it('displays age groups from database', function () {
    $ageGroups = AgeGroup::factory()->count(3)->sequence(
        ['label' => 'Ages 4–6', 'sort_order' => 1],
        ['label' => 'Ages 7–9', 'sort_order' => 2],
        ['label' => 'Ages 10–12', 'sort_order' => 3],
    )->create();

    get('/')
        ->assertSee('Ages 4–6')
        ->assertSee('Ages 7–9')
        ->assertSee('Ages 10–12');
});

it('stores the selected age group in user_settings', function () {
    $ageGroup = AgeGroup::factory()->create();

    Livewire::test('home-page')
        ->call('selectAgeGroup', $ageGroup->id);

    expect(UserSetting::get('age_group_id'))->toBe((string) $ageGroup->id);
});

it('pre-selects age group if already stored in user_settings', function () {
    $ageGroup = AgeGroup::factory()->create();
    UserSetting::set('age_group_id', (string) $ageGroup->id);

    Livewire::test('home-page')
        ->assertSet('selectedAgeGroupId', $ageGroup->id);
});

it('routes to fairgrounds map page from ride services button', function () {
    UserSetting::set('ride', 'D2R2');

    Livewire::test('home-page')
        ->call('routeToPage', 'fairgroundsmap')
        ->assertRedirect(route('fairgroundsmap'));
});

it('shows notify ride director button for non-guest users', function () {
    UserSetting::set('guest_user', 'false');

    Livewire::test('home-page')
        ->assertSee('Notify Ride Director');
});

it('hides notify ride director button for guest users', function () {
    UserSetting::set('guest_user', 'true');

    Livewire::test('home-page')
        ->assertDontSee('Notify Ride Director');
});

it('routes to notifiy page from ride services button', function () {
    UserSetting::set('guest_user', 'false');

    Livewire::test('home-page')
        ->call('routeToPage', 'notifiy')
        ->assertRedirect(route('notifiy'));
});

it('routes to merchandise page from ride services button', function () {
    Livewire::test('home-page')
        ->call('routeToPage', 'merchandise')
        ->assertRedirect(route('merchandise'));
});
