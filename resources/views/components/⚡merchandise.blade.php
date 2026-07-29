<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Native\Mobile\Facades\Browser;

new #[Title('Merchandise')] class extends Component {
    public string $shopUrl = 'https://d2r2.franklinlandtrust.org/shop/';

    public function mount(): void
    {
        if (function_exists('nativephp_call') && !app()->runningUnitTests()) {
            Browser::inApp($this->shopUrl);
        }
    }

    public function openShop(): void
    {
        Browser::open($this->shopUrl);
        //dd("Opening shop in app browser: {$this->shopUrl}");
    }
};
?>

<div class="min-h-screen px-4 py-12">
    <div class="mx-auto max-w-lg space-y-5">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-[#004030]">Merchandise</h1>
            <a href="{{ route('home') }}" wire:navigate
                class="text-sm font-semibold text-[#005040] transition-colors hover:text-[#004030]">&larr; Home</a>
        </div>

        <div class="space-y-4 rounded-2xl border-2 border-[#90b040] bg-white p-5 shadow-md shadow-[#005040]/10">
            <p class="text-base text-[#4a6b66]">
                The D2R2 merchandise shop opens in the app's native browser view.
            </p>

            <button type="button" wire:click="openShop"
                class="w-full min-h-[56px] rounded-2xl border-2 border-[#90b040] bg-[#005040] px-6 py-4 text-lg font-bold text-[#f2f8f5] shadow-md shadow-[#005040]/25 transition-all duration-200 hover:bg-[#004030] hover:shadow-lg">
                Open Merchandise Store
            </button>
        </div>
    </div>
</div>
