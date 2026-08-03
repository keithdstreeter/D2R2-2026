<?php

namespace App\Services;

use App\Models\UserSetting;
use Native\Mobile\Facades\PushNotifications;

class PushNotificationManager
{
    /**
     * @return array{available: bool, permission: ?string, token: ?string}
     */
    public function initialize(): array
    {
        $cachedToken = UserSetting::get('push_token');

        if (! $this->isNativeAvailable()) {
            return [
                'available' => false,
                'permission' => null,
                'token' => $cachedToken,
            ];
        }

        $permission = PushNotifications::checkPermission();
        if ($permission !== null) {
            UserSetting::set('push_permission', $permission);
        }

        if (in_array($permission, ['granted', 'not_determined', 'provisional', 'ephemeral'], true)) {
            PushNotifications::enroll()
                ->id('app-startup')
                ->remember()
                ->enroll();
        }

        $token = PushNotifications::getToken();
        if ($token !== null) {
            $this->storeToken($token);
        }

        return [
            'available' => true,
            'permission' => $permission,
            'token' => $token ?? $cachedToken,
        ];
    }

    public function storeToken(string $token): void
    {
        UserSetting::set('push_token', $token);
    }

    protected function isNativeAvailable(): bool
    {
        return function_exists('nativephp_call');
    }
}
