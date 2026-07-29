<?php

use App\Models\UserSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Notify Ride Director')] class extends Component {
    #[Validate('required|string|min:3|max:1000')]
    public string $message = '';

    public string $error = '';
    public string $success = '';

    public function mount(): void
    {
        if (UserSetting::get('Guest_User') !== 'false') {
            $this->redirect(route('home'), navigate: true);
        }
    }

    public function send(): void
    {
        $this->reset('error', 'success');
        $this->validate();

        try {
            $response = Http::api()->post('/ride-director/messages', [
                'message' => $this->message,
                'first_name' => UserSetting::get('first_name'),
                'last_name' => UserSetting::get('last_name'),
                'bib' => UserSetting::get('bib'),
                'ride' => UserSetting::get('ride'),
            ]);
        } catch (ConnectionException) {
            $this->error = 'Unable to send right now. Please check your connection and try again.';

            return;
        }

        if (!$response->successful()) {
            $this->error = $response->json('message') ?? 'Unable to send your message right now.';

            return;
        }

        $this->success = 'Message sent to the Ride Director.';
        $this->message = '';
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
            @if ($error)
                <div class="mb-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ $error }}
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
                    <textarea id="message" wire:model.live="message" rows="6"
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
