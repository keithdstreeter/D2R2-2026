<?php

use App\Models\UserSetting;
use App\Services\PushNotificationManager;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Native\Mobile\Attributes\OnNative;
use Native\Mobile\Events\PushNotification\PushNotificationReceived;
use Native\Mobile\Events\PushNotification\TokenGenerated;

new #[Title('Notify Ride Director')] class extends Component {
    #[Validate('required|string|min:3|max:1000')]
    public string $message = '';

    public string $dateSent = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $bib = '';
    public string $ride_short_name = '';

    public string $error = '';
    public string $success = '';
    public ?string $pushToken = null;
    public ?string $pushPermission = null;
    public string $lastPushMessage = '';

    public function mount(): void
    {
        if (UserSetting::get('guest_user') !== 'false') {
            $this->redirect(route('home'), navigate: true);
        }

        $this->initializePushNotifications();
    }

    public function initializePushNotifications(): void
    {
        $status = app(PushNotificationManager::class)->initialize();

        $this->pushToken = $status['token'];
        $this->pushPermission = $status['permission'];
    }

    public function send(): void
    {
        $this->reset('error', 'success');
        $this->validate();

        try {
            // $baseUrl = rtrim((string) config('services.api.url', 'http://localhost'), '/');
            // $endpoint = $baseUrl !== '' ? $baseUrl . '/auth/notifications' : 'http://localhost/auth/notifications';

            $baseUrl = config('services.api.url');
            $endpoint = $baseUrl . '/auth/notifications';

            //$response = Http::api()->post($baseUrl . '/auth/notifications', [

            $this->dateSent = now()->toIso8601String();
            $response = Http::api()->post($endpoint, [
                // 'date_sent' => $this->dateSent,
                // 'message' => $this->message,
                'first_name' => UserSetting::get('first_name'),
                'last_name' => UserSetting::get('last_name'),
                'bib' => UserSetting::get('bib'),
                'ride' => UserSetting::get('ride_id'),
                'ride_short_name' => UserSetting::get('ride_short_name'),
                'push_token' => UserSetting::get('push_token'),
                'DateSent' => $this->dateSent,
                'Message' => $this->message,
            ]);
        } catch (ConnectionException) {
            //$this->error = 'Unable to send right now. Please check your connection and try again.';
            $this->error = $response->body();
            return;
        }

        if (!$response->successful()) {
            $this->error = $response->json('message') ?? 'Unable to send your message right now.';
            $this->error = $this->error . $response->body();

            return;
        }

        $this->success = 'Message sent to the Ride Director.';
        //$this->success = $this->success . $response->body();
        $this->message = '';
    }

    #[OnNative(TokenGenerated::class)]
    public function handlePushTokenGenerated(string $token): void
    {
        app(PushNotificationManager::class)->storeToken($token);

        $this->pushToken = $token;
        $this->pushPermission = 'granted';
        $this->success = 'Push notifications are enabled on this device.';
    }

    #[OnNative(PushNotificationReceived::class)]
    public function handlePushReceived(array $data = []): void
    {
        $message = $data['payload']['message'] ?? ($data['message'] ?? null);

        if (is_string($message) && $message !== '') {
            $this->lastPushMessage = $message;
        } else {
            $this->lastPushMessage = json_encode($data, JSON_UNESCAPED_SLASHES) ?: 'Push received.';
        }
    }
};
?>

<div class="min-h-screen px-4 py-16">
    <div class="mx-auto max-w-lg space-y-5">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-[#004030]">Notify Ride Director</h1>
            <a href="{{ route('home') }}" wire:navigate
                class="text-sm font-semibold text-[#005040] transition-colors hover:text-[#004030]">&larr; Home</a>
        </div>


        {{-- Add details to call 911 immediately for any emergencies --}}
        <div class="mb-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-lg font-medium text-red-700">
            <strong>Emergency?</strong> &nbsp; &nbsp; If this is an emergency, please call 911.
        </div>


        <div class="rounded-2xl border-2 border-[#90b040] bg-white p-5 shadow-md shadow-[#005040]/10">
            <div class="mb-4 rounded-xl border border-[#c0e0d0] bg-[#f7fbf8] px-4 py-3 text-sm text-[#163833]">
                <p><strong>Push Permission:</strong> {{ $pushPermission ?? 'unknown' }}</p>
                <p class="mt-1 break-all"><strong>Device Token:</strong> {{ $pushToken ?? 'Not available yet' }}</p>
                @if ($lastPushMessage)
                    <p class="mt-2"><strong>Last Push Message:</strong> {{ $lastPushMessage }}</p>
                @endif
            </div>

            @if ($error)
                <div class="mb-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ $error }}
                </div>
                <div class="mb-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ $this->getErrorBag() }}
                </div>
            @endif

            @if ($success)
                <div
                    class="mb-4 rounded-xl border border-[#90b040] bg-[#f4faea] px-4 py-3 text-sm font-medium text-[#005040]">
                    {{ $success }}
                </div>
            @endif



            <form wire:submit="send" class="space-y-4">
                <div class="space-y-1">
                    <label for="message" class="pl-1 text-sm font-semibold text-[#005040]">Message</label>
                    <input type="hidden" name="date_sent" wire:model.defer="dateSent">
                    <input type="hidden" name="first_name" wire:model.defer="first_name">
                    <input type="hidden" name="last_name" wire:model.defer="last_name">
                    <input type="hidden" name="bib" wire:model.defer="bib">
                    <input type="hidden" name="ride_short_name" wire:model.defer="ride_short_name">
                    <textarea id="message" name="message" wire:model.live="message" rows="6"
                        placeholder="Type your message to the Ride Director..."
                        class="w-full rounded-2xl border-2 border-[#c0e0d0] bg-white px-4 py-3 text-base text-[#163833] transition-colors focus:border-[#005040] focus:outline-none"></textarea>
                    @error('message')
                        <p class="pl-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full min-h-[56px] rounded-2xl border-2 border-[#90b040] bg-[#005040] px-6 py-4 text-lg font-bold text-[#f2f8f5] shadow-md shadow-[#005040]/25 transition-all duration-200 hover:bg-[#004030] hover:shadow-lg">
                    <span wire:loading.remove wire:target="send">Send to Ride Director</span>
                    <span wire:loading wire:target="send">Sending...</span>
                </button>
            </form>
        </div>
    </div>
</div>
