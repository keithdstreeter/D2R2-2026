<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\UserSetting;
use App\Services\PushNotificationManager;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('D2R2 Rider App')] class extends Component {
    public string $savedRide = '';
    public ?int $savedRideId = null;
    //      public string $savedRide = '';
    public string $savedRideShortName = '';
    public bool $isGuestUser = true;

    public function mount(): void
    {
        app(PushNotificationManager::class)->initialize();

        $savedRideId = UserSetting::get('ride_id');

        $savedRideShortName = UserSetting::get('ride_short_name');

        $this->savedRide = $savedRide ?? '';
        $this->savedRideShortName = $savedRideShortName ?? '';
        $this->isGuestUser = UserSetting::get('Guest_User') !== 'false';
    }

    public function selectAgeGroup(int $ageGroupId): void
    {
        $this->selectedAgeGroupId = $ageGroupId;
        UserSetting::set('age_group_id', (string) $ageGroupId);
        unset($this->hasMovies);
    }

    public function routeToPage(string $page): void
    {
        // $this->selectedAgeGroupId = $ageGroupId;
        // UserSetting::set('age_group_id', (string) $ageGroupId);
        // unset($this->hasMovies);
        if ($page === 'cuesheet') {
            $this->redirect(route('cuesheet'));
        } elseif ($page === 'map') {
            $this->redirect(route('image'));
        } elseif ($page === 'allroutesmap') {
            $this->redirect(route('allroutesmap'));
        } elseif ($page === 'fairgroundsmap') {
            $this->redirect(route('fairgroundsmap'));
        } elseif ($page === 'notifiy') {
            $this->redirect(route('notifiy'));
        } elseif ($page === 'merchandise') {
            $this->redirect(route('merchandise'));
        }
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

<div class="min-h-screen flex flex-col items-center justify-center px-4 py-18">

    <img src="{{ asset('images/logo.png') }}" alt="FLT Logo" class="img-fluid img-scale mb-6" style="max-width: 200px;">

    {{-- <img src="{{ asset('images/logo.png') }}" alt="Static Image" /> --}}

    <div class="w-full max-w-lg text-center" x-data="{ shown: false }" x-init="$nextTick(() => shown = true)">
        <div x-show="shown" x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <h1 class="mb-2 text-5xl font-bold text-[#004030]">
                D2R2 Rider App
            </h1>
            <p class="mb-10 text-lg text-[#4a6b66]">Enjoy the {{ $this->savedRideShortName }} ride!</p>
        </div>

        {{-- <h2 class="mb-4 text-xl font-semibold text-[#005040]">Ride Services</h2> --}}

        <div class="grid gap-3">
            {{-- @foreach ($this->ageGroups as $index => $ageGroup)
                <button
                    wire:key="age-group-{{ $ageGroup->id }}"
                    wire:click="selectAgeGroup({{ $ageGroup->id }})"
                    x-data="{ pressed: false }"
                    x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                    :class="pressed ? 'scale-95' : 'scale-100'"
                    @class([
                        'w-full rounded-2xl px-6 py-5 text-lg font-bold transition-all duration-200 min-h-[56px]',
                        'bg-gradient-to-r from-ocean-500 to-candy-500 text-white shadow-lg shadow-ocean-200 ring-2 ring-ocean-300' => $selectedAgeGroupId === $ageGroup->id,
                        'bg-white/80 text-gray-700 border-2 border-white hover:border-ocean-300 hover:shadow-md backdrop-blur-sm' => $selectedAgeGroupId !== $ageGroup->id,
                    ])
                    style="animation: fade-in-up 0.4s ease-out {{ $index * 0.08 }}s both"
                >
                    {{ $ageGroup->label }}
                </button>
            @endforeach --}}

            <button wire:key="cuesheet" wire:click="routeToPage('cuesheet')" x-data="{ pressed: false }"
                x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                :class="pressed ? 'scale-95' : 'scale-100'"
                class="w-full min-h-[56px] rounded-2xl border-2 border-[#90b040] bg-[#005040] px-6 py-5 text-lg font-bold text-[#f2f8f5] shadow-md shadow-[#005040]/25 transition-all duration-200 hover:bg-[#004030] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#c0e0f0] focus:ring-offset-2">
                {{ $this->savedRideShortName }} Cue Sheet
            </button>

            <button wire:key="map" wire:click="routeToPage('map')" x-data="{ pressed: false }"
                x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                :class="pressed ? 'scale-95' : 'scale-100'"
                class="w-full min-h-[56px] rounded-2xl border-2 border-[#90b040] bg-[#005040] px-6 py-5 text-lg font-bold text-[#f2f8f5] shadow-md shadow-[#005040]/25 transition-all duration-200 hover:bg-[#004030] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#c0e0f0] focus:ring-offset-2">
                {{ $this->savedRideShortName }} Map
            </button>

            <button wire:key="allroutesmap" wire:click="routeToPage('allroutesmap')" x-data="{ pressed: false }"
                x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                :class="pressed ? 'scale-95' : 'scale-100'"
                class="w-full min-h-[56px] rounded-2xl border-2 border-[#90b040] bg-[#005040] px-6 py-5 text-lg font-bold text-[#f2f8f5] shadow-md shadow-[#005040]/25 transition-all duration-200 hover:bg-[#004030] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#c0e0f0] focus:ring-offset-2">
                All Routes Map
            </button>

            <button wire:key="fairgrounds" wire:click="routeToPage('fairgroundsmap')" x-data="{ pressed: false }"
                x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                :class="pressed ? 'scale-95' : 'scale-100'"
                class="w-full min-h-[56px] rounded-2xl border-2 border-[#90b040] bg-[#005040] px-6 py-5 text-lg font-bold text-[#f2f8f5] shadow-md shadow-[#005040]/25 transition-all duration-200 hover:bg-[#004030] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#c0e0f0] focus:ring-offset-2">
                Fairgrounds Maps
            </button>

            <button wire:key="merchandise" wire:click="routeToPage('merchandise')" x-data="{ pressed: false }"
                x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                :class="pressed ? 'scale-95' : 'scale-100'"
                class="w-full min-h-[56px] rounded-2xl border-2 border-[#90b040] bg-[#005040] px-6 py-5 text-lg font-bold text-[#f2f8f5] shadow-md shadow-[#005040]/25 transition-all duration-200 hover:bg-[#004030] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#c0e0f0] focus:ring-offset-2">
                Merchandise
            </button>

            @if (!$isGuestUser)
                <button wire:key="notifiy" wire:click="routeToPage('notifiy')" x-data="{ pressed: false }"
                    x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                    :class="pressed ? 'scale-95' : 'scale-100'"
                    class="w-full min-h-[56px] rounded-2xl border-2 border-[#90b040] bg-[#005040] px-6 py-5 text-lg font-bold text-[#f2f8f5] shadow-md shadow-[#005040]/25 transition-all duration-200 hover:bg-[#004030] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#c0e0f0] focus:ring-offset-2">
                    Notify Ride Director
                </button>
            @endif

            {{-- 
            <button wire:key="settings" wire:click="routeToPage('settings')" x-data="{ pressed: false }"
                x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                :class="pressed ? 'scale-95' : 'scale-100'" @class([
                    'w-full rounded-2xl px-6 py-5 text-lg font-bold transition-all duration-200 min-h-[56px]',
                    'bg-gradient-to-r from-ocean-500 to-candy-500 text-white shadow-lg shadow-ocean-200 ring-2 ring-ocean-300',
                    'bg-white/80 text-gray-700 border-2 border-white hover:border-ocean-300 hover:shadow-md backdrop-blur-sm',
                ]) 
                Settings
            </button> --}}
        </div>

        {{-- @if ($selectedAgeGroupId && $this->hasMovies)
            <div class="mt-8 animate-fade-in-up">
                <button
                    wire:click="quickStart"
                    x-data="{ pressed: false }"
                    x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                    :class="pressed ? 'scale-95' : 'scale-100'"
                    class="w-full rounded-2xl bg-gradient-to-r from-mint-500 to-mint-400 px-6 py-5 text-lg font-bold text-white shadow-lg shadow-mint-200 hover:shadow-xl transition-all duration-200 min-h-[56px]"
                >
                    Browse Movies
                </button>
            </div>
        @endif --}}

        <div class="mt-8 flex items-center justify-center gap-6 text-2xl text-black">

            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
            <a href="{{ route('settings') }}" wire:navigate
                class="text-lg font-semibold text-[#005040] transition-colors hover:text-[#004030]">
                Settings / Change Ride
            </a>
            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
        </div>
    </div>
</div>
