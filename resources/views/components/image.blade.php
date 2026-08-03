<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\UserSetting;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Map View')] class extends Component {
    public ?int $selectedAgeGroupId = null;

    public ?string $image_url = null;
    public ?string $rideShortName = null;

    public function mount(): void
    {
        // Get the Ride (need better names across the board)
        $this->rideShortName = UserSetting::get('ride_short_name') ?: null;
        // Make the Map Name (currently using PNG files)
        $this->image_url = 'map-' . $this->rideShortName . '.png';
    }
};
?>

<div class="min-h-screen px-4 py-16">

    {{-- Title and Back Button --}}
    <div class="mx-auto max-w-lg mt- 20">
        <div class="mb-6 px-4 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">{{ $this->rideShortName }} Map</h1>

            <a href="{{ route('home') }}" wire:navigate
                class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">&larr; Back</a>
        </div>
    </div>

    <div class="overflow-auto rounded-lg bg-white/70 p-2">
        <img src="{{ asset('images/' . $this->image_url) }}" alt="Ride map"
            class="h-auto w-full max-w-none [touch-action:pinch-zoom]">
    </div>

</div>
