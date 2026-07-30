<?php

use App\Models\UserSetting;
use App\Services\DeviceIdentity;
use Native\Mobile\Facades\Device;

it('generates a default username from device id', function () {
    $service = app(DeviceIdentity::class);
    $deviceId = $service->getDeviceId();

    $expectedSuffix = strtoupper(substr($deviceId, -6));
    expect($service->getUsername())->toBe('User'.$expectedSuffix);
});

it('stores device identity in UserSetting on first call', function () {
    $service = app(DeviceIdentity::class);
    $deviceId = $service->getDeviceId();

    expect(UserSetting::get('device_id'))->toBe($deviceId)
        ->and(UserSetting::get('username'))->not->toBeNull();
});

it('returns the stored device id on subsequent calls', function () {
    $service = app(DeviceIdentity::class);
    $firstId = $service->getDeviceId();
    $secondId = $service->getDeviceId();

    expect($secondId)->toBe($firstId);
});

it('generates a random id when Device plugin is unavailable', function () {
    $service = app(DeviceIdentity::class);
    $deviceId = $service->getDeviceId();

    // Outside NativePHP runtime, it should generate a UUID
    expect($deviceId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
});

it('returns fallback device info when NativePHP is unavailable', function () {
    $service = app(DeviceIdentity::class);
    $info = $service->getDeviceInfo();

    expect($info)->toBe([
        'model' => 'Unknown',
        'os' => 'Unknown',
        'platform' => 'Unknown',
    ]);
});

it('parses native device info payload including osVersion', function () {
    Device::shouldReceive('getInfo')
        ->once()
        ->andReturn('{"model":"iPhone 15","platform":"ios","osVersion":"18.5"}');

    $service = new class extends DeviceIdentity
    {
        protected function isNativeAvailable(): bool
        {
            return true;
        }
    };

    $info = $service->getDeviceInfo();

    expect($info)->toBe([
        'model' => 'iPhone 15',
        'os' => '18.5',
        'platform' => 'ios',
    ]);
});

it('reads native device id when available', function () {
    Device::shouldReceive('getId')
        ->once()
        ->andReturn('native-device-id-123');

    $service = new class extends DeviceIdentity
    {
        protected function isNativeAvailable(): bool
        {
            return true;
        }
    };

    $deviceId = $service->getDeviceId();

    expect($deviceId)->toBe('native-device-id-123')
        ->and(UserSetting::get('device_id'))->toBe('native-device-id-123');
});
