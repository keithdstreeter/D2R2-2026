<?php

use App\Models\AgeGroup;
use App\Models\QuizAnswer;
use App\Models\QuizSession;
use App\Models\UserSetting;
use App\Models\Ride;
use App\Services\DeviceIdentity;
use App\Services\LeaderboardService;
use App\Services\NativeFeedback;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Facades\Dialog;

new #[Title('Settings — D2R2 App')] class extends Component {
    public bool $gateUnlocked = false;

    public ?int $selectedAgeGroupId = null;

    public bool $soundEnabled = true;

    public bool $hapticsEnabled = true;

    public bool $showResetConfirm = false;

    public bool $resetComplete = false;

    #[Validate('required|alpha_num|min:3|max:20')]
    public string $username = '';

    public bool $usernameSaved = false;

    /** @var array{model: string, os: string, platform: string} */
    public array $deviceInfo = [];

    public ?int $savedRideId = null;
    //public ?string $ride = null;

    public string $ride_list = '';

    public function mount(): void
    {
        $savedId = UserSetting::get('age_group_id');
        //$savedRideId = UserSetting::get('ride_id');
        //$savedDistanceType = UserSetting::get('distance_type');
        $savedBib = UserSetting::get('bib');
        $savedFirstName = UserSetting::get('first_name');
        $savedLastName = UserSetting::get('last_name');
        $savedRideId = UserSetting::get('ride_id');
        $savedRideShortName = UserSetting::get('ride_short_name');

        if (is_string($savedRideShortName) && $savedRideShortName !== '') {
            $this->ride_list = strtolower($savedRideShortName);
        }
        //dd($this->ride_list);
        // Values seem correctly saved in UserSetting

        //dd($savedRideId, $savedBib, $savedFirstName, $savedLastName);
        // if ($savedId) {
        //     $this->selectedAgeGroupId = (int) $savedId;
        // }

        if ($savedRideId) {
            $this->savedRideId = (int) $savedRideId;
        }
        $this->soundEnabled = UserSetting::get('sound_enabled') !== '0';
        $this->hapticsEnabled = UserSetting::get('haptics_enabled') !== '0';

        $identity = app(DeviceIdentity::class);
        // This works, but show Debug info instead TEMPORARY
        $this->username = $savedFirstName . ' ' . $savedLastName;

        //$this->username = $savedRideDesc . ' - ' . $savedRideId . ' - ' . $savedRide;
        //$identity->getUsername();
        $this->deviceInfo = $identity->getDeviceInfo();

        //dd($this->savedRideId, $this->ride);
    }

    public function saveSettings(): void
    {
        $ride_data = Ride::query()
            ->whereRaw('LOWER(ride) = ?', [strtolower($this->ride_list)])
            ->first();
        //dd($ride_data);
        if ($ride_data) {
            // If ride data is found, save the ride_id, ride_desc, and ride to UserSettings
            UserSetting::set('ride_id', $ride_data->id);
            UserSetting::set('ride_desc', $ride_data->ride_desc);
            UserSetting::set('ride_short_name', strtolower($ride_data->ride));
            UserSetting::set('ride_list', strtolower($ride_data->ride));
        }

        // Add Save for the Username here as well, remove separate form code
        // $this->validate();

        $identity = app(DeviceIdentity::class);
        $identity->setUsername($this->username);

        app(LeaderboardService::class)->syncUsername($identity->getDeviceId(), $this->username);

        $this->usernameSaved = true;

        //dd($ride_data);
        $this->redirect(route('home'));
    }

    public function updatedRideList(string $value): void
    {
        $ride_data = Ride::query()
            ->whereRaw('LOWER(ride) = ?', [strtolower($value)])
            ->first();

        if ($ride_data) {
            UserSetting::set('ride_short_name', strtolower($ride_data->ride));
            UserSetting::set('ride_id', (string) $ride_data->id);
            UserSetting::set('ride_desc', $ride_data->ride_desc);
            UserSetting::set('ride_list', strtolower($ride_data->ride));
        }
    }

    #[On('parent-gate-passed')]
    public function unlockGate(): void
    {
        $this->gateUnlocked = true;
    }

    public function changeAgeGroup(int $ageGroupId): void
    {
        $this->selectedAgeGroupId = $ageGroupId;
        UserSetting::set('age_group_id', (string) $ageGroupId);
    }

    public function toggleSound(): void
    {
        $this->soundEnabled = !$this->soundEnabled;
        UserSetting::set('sound_enabled', $this->soundEnabled ? '1' : '0');
    }

    public function toggleHaptics(): void
    {
        $this->hapticsEnabled = !$this->hapticsEnabled;
        UserSetting::set('haptics_enabled', $this->hapticsEnabled ? '1' : '0');
    }

    // public function updateUsername(): void
    // {
    //     $this->validate();

    //     $identity = app(DeviceIdentity::class);
    //     $identity->setUsername($this->username);

    //     app(LeaderboardService::class)->syncUsername($identity->getDeviceId(), $this->username);

    //     $this->usernameSaved = true;
    // }

    public function confirmReset(): void
    {
        if (function_exists('nativephp_call')) {
            Dialog::alert('Reset Progress', 'This will delete all quiz history and cannot be undone.', ['Cancel', 'Yes, Reset'])
                ->id('reset-progress')
                ->remember()
                ->show();

            return;
        }

        $this->showResetConfirm = true;
    }

    #[On('native:' . ButtonPressed::class)]
    public function handleAlertButton(int $index, string $label, ?string $id = null): void
    {
        if ($id === 'reset-progress' && $index === 1) {
            $this->resetProgress();
        }
    }

    public function cancelReset(): void
    {
        $this->showResetConfirm = false;
    }

    public function resetProgress(): void
    {
        QuizAnswer::query()->delete();
        QuizSession::query()->delete();
        $this->showResetConfirm = false;
        $this->resetComplete = true;
    }

    public function logout(): void
    {
        // Clear the guest_user setting and any authentication tokens
        UserSetting::set('guest_user', 'false');
        session()->forget(['auth_token', 'token_verified_at']);
        $this->redirect(route('login'), navigate: true);
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, AgeGroup> */
    #[Computed]
    public function ageGroups(): \Illuminate\Database\Eloquent\Collection
    {
        return AgeGroup::active()->orderBy('sort_order')->get();
    }
};
?>

<div class="min-h-screen px-4 py-16">
    <div class="mx-auto max-w-lg">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Settings</h1>
            {{-- <a href="{{ route('home') }}" wire:navigate
                class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">&larr; Home</a> --}}
            <button type="button" wire:click="saveSettings()">&larr; Save and Return</button>
        </div>

        {{-- @if (!$gateUnlocked)
            <livewire:parent-gate />
        @else --}}
        <div class="space-y-6">
            {{-- Age Group --}}
            <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 animate-fade-in-up"
                style="animation-delay: 0s">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Ride</h2>
                <div class="grid gap-3">
                    <select wire:model.live="ride_list" id="ride_list" name="ride_list"
                        class="w-full rounded-2xl border-2 border-white bg-white/60 px-4 py-3 text-base text-gray-700 focus:border-ocean-300 focus:outline-none transition-colors">
                        {{-- <option disabled value="">Select a ride...</option>      --}}


                        {{-- EXAMPLE CODE - Might work to solve issues --}}
                        {{-- <select id="ride_list" wire:model.live="ride_list->id" value="{{ $ride_list->id }}"
                            class="text-black">
                            <option value="">No Ride selected</option>
                            @foreach ($ride_list as $r_id)
                                <option wire:key="{{ $r_id->id }}" value="{{ $r_id->desc }}"
                                    {{ $ride_list_Id == $r_id->id ? 'selected' : '' }}>
                                    {{ $r_id->desc }}
                                </option>
                            @endforeach
                        </select> --}}

                        <option value="">Select a ride...</option>
                        @foreach (Ride::all(['id', 'ride', 'ride_desc']) as $ride)
                            <option wire:key="ride-option-{{ $ride->id }}" value="{{ strtolower($ride->ride) }}">
                                {{ $ride->ride_desc }}
                            </option>
                            {{-- if ($ride_list->id == $savedRideId) {
                                    <option value="{{ $ride_list->id }}" selected>{{ $ride_list->id }} - {{ $savedRideId }}  {{ $ride_list->ride_desc }}</option>
                                } else {
                                    <option value="{{ $ride_list->id }}">{{ $ride_list->id }} - {{ $savedRideId }}  {{ $ride_list->ride_desc }}</option>
                                }    --}}
                            {{-- {{ $ride_list->id }}  {{ $this->savedRideId }}   --}}
                            {{-- <option value="{{ $ride_list->id }}">{{ $ride_list->ride_desc }}</option> --}}
                        @endforeach
                        {{-- <option value="999" selected>KDS OTHER</option> --}}
                    </select>

                    {{-- @foreach ($this->ageGroups as $ageGroup)
                            <button
                                wire:key="age-group-{{ $ageGroup->id }}"
                                wire:click="changeAgeGroup({{ $ageGroup->id }})"
                                x-data="{ pressed: false }"
                                x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                                :class="pressed ? 'scale-95' : 'scale-100'"
                                @class([
                                    'w-full rounded-2xl px-5 py-4 text-base font-bold transition-all duration-200 min-h-[52px]',
                                    'bg-gradient-to-r from-ocean-500 to-candy-500 text-white shadow-lg shadow-ocean-200' => $selectedAgeGroupId === $ageGroup->id,
                                    'bg-white/60 text-gray-700 border-2 border-white hover:border-ocean-300' => $selectedAgeGroupId !== $ageGroup->id,
                                ])
                            >
                                {{ $ageGroup->label }}
                            </button>
                        @endforeach --}}
                </div>
            </div>

            {{-- Username --}}
            <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 animate-fade-in-up"
                style="animation-delay: 0.1s">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Username</h2>
                {{-- <form wire:submit="updateUsername" class="space-y-3"> --}}
                <div>
                    <input type="text" wire:model="username"
                        class="w-full rounded-2xl border-2 border-white bg-white/60 px-4 py-3 text-base text-gray-700 focus:border-ocean-300 focus:outline-none transition-colors"
                        placeholder="Enter username" maxlength="20" />
                    @error('username')
                        <p class="mt-1 text-sm text-candy-500">{{ $message }}</p>
                    @enderror
                </div>
                {{-- <button type="submit"
                        class="w-full rounded-2xl bg-gradient-to-r from-ocean-500 to-candy-500 px-4 py-3 text-sm font-bold text-white transition-all duration-200 min-h-[44px]">
                        Save Username
                    </button>
                    @if ($usernameSaved)
                        <p class="text-sm text-mint-500 font-semibold animate-fade-in">Username saved!</p>
                    @endif
                {{-- </form> --}}
            </div>

            {{-- Sound & Haptics --}}
            {{-- <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 animate-fade-in-up"
                style="animation-delay: 0.2s">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Preferences</h2>
                <div class="space-y-5">
                    <div class="flex items-center justify-between min-h-[44px]">
                        <span class="text-base text-gray-700">Sound Effects</span>
                        <button wire:click="toggleSound" @class([
                            'relative inline-flex h-8 w-14 items-center rounded-full transition-colors duration-200',
                            'bg-ocean-500' => $soundEnabled,
                            'bg-gray-300' => !$soundEnabled,
                        ])>
                            <span @class([
                                'inline-block h-6 w-6 rounded-full bg-white shadow-sm transition-transform duration-200',
                                'translate-x-7' => $soundEnabled,
                                'translate-x-1' => !$soundEnabled,
                            ])></span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between min-h-[44px]">
                        <span class="text-base text-gray-700">Haptic Feedback</span>
                        <button wire:click="toggleHaptics" @class([
                            'relative inline-flex h-8 w-14 items-center rounded-full transition-colors duration-200',
                            'bg-ocean-500' => $hapticsEnabled,
                            'bg-gray-300' => !$hapticsEnabled,
                        ])>
                            <span @class([
                                'inline-block h-6 w-6 rounded-full bg-white shadow-sm transition-transform duration-200',
                                'translate-x-7' => $hapticsEnabled,
                                'translate-x-1' => !$hapticsEnabled,
                            ])></span>
                        </button>
                    </div>
                </div>
            </div> --}}

            {{-- Reset Progress --}}
            {{-- <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 animate-fade-in-up"
                style="animation-delay: 0.3s">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Data</h2>

                @if ($resetComplete)
                    <p class="text-sm text-mint-500 font-semibold mb-3 animate-fade-in">All progress has been reset.</p>
                @endif

                @if ($showResetConfirm)
                    <p class="text-sm text-gray-600 mb-3">Are you sure? This will delete all quiz history and cannot be
                        undone.</p>
                    <div class="flex gap-3">
                        <button wire:click="resetProgress"
                            class="flex-1 rounded-2xl bg-candy-500 px-4 py-4 text-sm font-bold text-white hover:bg-candy-600 transition-all duration-200 min-h-[48px]">
                            Yes, Reset
                        </button>
                        <button wire:click="cancelReset"
                            class="flex-1 rounded-2xl border-2 border-white bg-white/60 px-4 py-4 text-sm font-bold text-gray-600 hover:border-gray-300 transition-all duration-200 min-h-[48px]">
                            Cancel
                        </button>
                    </div>
                @else
                    <button wire:click="confirmReset"
                        class="w-full rounded-2xl border-2 border-candy-200 px-4 py-4 text-sm font-bold text-candy-500 hover:bg-candy-50 transition-all duration-200 min-h-[48px]">
                        Reset All Progress
                    </button>
                @endif
            </div> --}}

            {{-- Device Info --}}
            <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 animate-fade-in-up"
                style="animation-delay: 0.4s">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Mobile Device Info</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between min-h-[36px]">
                        <span class="text-sm text-gray-500">Model</span>
                        <span class="text-sm font-medium text-gray-700">{{ $deviceInfo['model'] }}</span>
                    </div>
                    <div class="flex items-center justify-between min-h-[36px]">
                        <span class="text-sm text-gray-500">OS</span>
                        <span class="text-sm font-medium text-gray-700">{{ $deviceInfo['os'] }}</span>
                    </div>
                    <div class="flex items-center justify-between min-h-[36px]">
                        <span class="text-sm text-gray-500">Platform</span>
                        <span class="text-sm font-medium text-gray-700">{{ $deviceInfo['platform'] }}</span>
                    </div>
                </div>
            </div>
        </div>
        {{-- @endif --}}

        {{-- Account --}}
        <div class="mt-6 rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 animate-fade-in-up">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Account</h2>
            <button wire:click="logout"
                class="w-full rounded-2xl border-2 border-candy-200 px-4 py-4 text-sm font-bold text-candy-500 hover:bg-candy-50 transition-all duration-200 min-h-[48px]">
                Log Out
            </button>
        </div>
    </div>
</div>
