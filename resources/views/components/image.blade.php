<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\UserSetting;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Image View')] class extends Component {
    public ?int $selectedAgeGroupId = null;

    public ?string $image_url = null;
    public ?string $ride_id = null;

    public function mount(): void
    {
        //$ride_id = UserSetting::get('ride_id');

        $this->ride_id = UserSetting::get('ride_id') ?: null;

        // if ($savedId) {
        //     $this->selectedAgeGroupId = (int) $savedId;
        // }

        $this->image_url = 'map-' . $this->ride_id . '.png';

        //dd($this->image_url);
        //dd($this->image_url);
        //$path = public_path('css/app.css');
    }

    public function selectAgeGroup(int $ageGroupId): void
    {
        $this->selectedAgeGroupId = $ageGroupId;
        UserSetting::set('age_group_id', (string) $ageGroupId);
        unset($this->hasMovies);
    }

    public function quickStart(): void
    {
        if (!$this->selectedAgeGroupId) {
            return;
        }

        $this->redirect(route('movies'));
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, AgeGroup> */
    #[Computed]
    public function ageGroups(): \Illuminate\Database\Eloquent\Collection
    {
        return AgeGroup::active()->orderBy('sort_order')->get();
    }

    #[Computed]
    public function hasMovies(): bool
    {
        if (!$this->selectedAgeGroupId) {
            return false;
        }

        return Movie::active()->where('age_group_id', $this->selectedAgeGroupId)->exists();
    }
};
?>

<div class="min-h-screen px-4 py-8">

    <div class="mx-auto max-w-lg mt- 20">
        {{-- Title and Back Button --}}
        <div class="mb-6 px-4 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">{{ $this->ride_id }} Map</h1>
            {{-- {{ implode(', ', $this->updateRideIds) }} --}}

            {{-- <button type="button" wire:click="showVals($updateRideIds)">
                Show
            </button> --}}

            {{-- <a href="{{ route('home') }}" wire:navigate
                class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">&larr; Back</a> --}}
            {{-- <button type="button" wire:click="saveChecks()">Back</button> --}}

            {{-- <button type="submit">Complete</button> --}}
            <a href="{{ route('home') }}" wire:navigate
                class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">&larr; Back</a>
            {{-- <a href="#" wire:click.prevent="saveChecks($updateRideIds)">GO HOME</a> --}}
        </div>
    </div>
    {{-- <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12"> --}}

    {{-- Images are not showing in the simulator --}}
    <img src="{{ $this->image_url }}" alt="Photo" class="img-fluid">


</div>
