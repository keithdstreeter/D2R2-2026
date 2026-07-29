<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\UserSetting;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('All Routes Map')] class extends Component {
    public ?int $selectedAgeGroupId = null;

    public ?string $image_url = null;
    public ?string $ride_id = null;

    public function mount(): void
    {
        // Get the Ride (need better names across the board)
        //$this->ride_id = UserSetting::get('ride') ?: null;
        // Make the Map Name (currently using PNG files)
        $this->image_url = 'All-Routes.png';
    }
};
?>

<div class="min-h-screen px-4 py-16">

    {{-- Title and Back Button --}}
    <div class="mx-auto max-w-lg mt- 20">
        <div class="mb-6 px-4 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">All D2R2 Routes Map</h1>

            <a href="{{ route('home') }}" wire:navigate
                class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">&larr; Back</a>
        </div>
    </div>

    {{-- Images are not showing in the simulator --}}
    <img src="{{ asset('images/' . $this->image_url) }}" alt="Photo" class="img-fluid">

</div>
