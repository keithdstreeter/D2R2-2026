<?php

use App\Models\UserSetting;
use App\Services\PushNotificationManager;
use Native\Mobile\Facades\PushNotifications;
use Native\Mobile\PendingPushNotificationEnrollment;

it('returns cached token when native runtime is unavailable', function () {
    UserSetting::set('push_token', 'cached-token-1');

    $manager = new class extends PushNotificationManager
    {
        protected function isNativeAvailable(): bool
        {
            return false;
        }
    };
    $status = $manager->initialize();

    expect($status)->toBe([
        'available' => false,
        'permission' => null,
        'token' => 'cached-token-1',
    ]);
});

it('initializes push notifications and stores token when native runtime is available', function () {
    PushNotifications::shouldReceive('checkPermission')
        ->once()
        ->andReturn('granted');
    PushNotifications::shouldReceive('enroll')
        ->once()
        ->andReturn(new PendingPushNotificationEnrollment);
    PushNotifications::shouldReceive('getToken')
        ->once()
        ->andReturn('native-token-xyz');

    $manager = new class extends PushNotificationManager
    {
        protected function isNativeAvailable(): bool
        {
            return true;
        }
    };

    $status = $manager->initialize();

    expect($status)->toBe([
        'available' => true,
        'permission' => 'granted',
        'token' => 'native-token-xyz',
    ])
        ->and(UserSetting::get('push_permission'))->toBe('granted')
        ->and(UserSetting::get('push_token'))->toBe('native-token-xyz');
});
