<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\UserSetting;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Fairgrounds Info')] class extends Component {
    public ?int $selectedAgeGroupId = null;

    public ?string $image_url = null;
    public ?string $ride_id = null;

    public function mount(): void
    {
        // Get the Ride (need better names across the board)
        //$this->ride_id = UserSetting::get('ride') ?: null;
        // Make the Map Name (currently using PNG files)
        $this->map1_url = 'Fair Map D2R2 2026.png';

        $this->map2_url = 'D2R2 Map.png';

        $this->map3_url = 'Fairgrounds Map.png';
    }
};
?>

<div class="min-h-screen px-4 py-8">

    {{-- Title and Back Button --}}
    <div class="mx-auto max-w-lg mt- 20">
        <div class="mb-6 px-4 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Fairgrounds - 3 Maps</h1>

            <a href="{{ route('home') }}" wire:navigate
                class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">&larr; Back</a>
        </div>
    </div>

    {{-- Images are not showing in the simulator --}}
    <p>Overhead Satellite View</p>
    <img src="{{ $this->map1_url }}" alt="Photo" class="img-fluid">
    <br>
    <p>Angle Overhead View</p>
    <img src="{{ $this->map2_url }}" alt="Photo" class="img-fluid">
    <br>
    <p>Detailed Drawing</p>
    <img src="{{ $this->map3_url }}" alt="Photo" class="img-fluid">

</div>
