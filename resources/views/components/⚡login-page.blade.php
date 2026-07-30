<?php

use App\Models\Registration;
use App\Models\Ride;
use App\Models\UserSetting;
use App\Services\DeviceIdentity;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Native\Mobile\Facades\Browser;
use Illuminate\Http\JsonResponse;

new #[Title('Login')] class extends Component {
    #[Validate('required|email')]
    public string $email = '';

    public string $first_name = '';
    public string $last_name = '';
    public string $bib = '';
    public string $dob_day = '';
    public string $dob_month = '';
    public string $dob_year = '';
    public string $dob_full = '';

    //public string $password = '';

    #[Validate('required')]
    public string $error = '';

    public function mount(): void
    {
        if (session('auth_token')) {
            $this->redirect(route('home'), navigate: true);
        }

        // Temrporary: pre-fill credentials for easier testing during development

        // Christopher	Capeliini	(914) 419-9634	100k	chris@chriscap.com	3/7/77
        // $this->email = 'kds@kds.com';
        // $this->password = 'password';
        $this->first_name = 'Christopher';
        $this->last_name = 'Capeliini';
        $this->dob_full = '3/7/77';
        $this->dob_month = '3';
        $this->dob_day = '7';
        $this->dob_year = '1977';
        //$this->bib = '519';

        // Set the Guest_User setting to true when the login page is mounted
        // Validated Users will have additional features (Communications, etc.)
        // that Guest Users will not have access to.
        UserSetting::set('Guest_User', 'true');
    }

    public function getRideID($rideName): int
    {
        // Retrieve the ride ID from the Ride model based on the CategoryEntered
        // value (ride name) from the Registration model
        //$rideId = UserSetting::get('ride_id');

        //dd($rideName);
        $rideId = Ride::where('ride', $rideName)->pluck('id')->first();

        // If the ride ID is not set, return a default value (e.g., 0)
        return $rideId ? (int) $rideId : 0;
    }

    public function guestLogin(): void
    {
        $this->reset('error');

        UserSetting::set('Guest_User', 'true');
        $token = bcrypt('D2R2Guest');
        session(['auth_token' => $token, 'token_verified_at' => now()]);
        UserSetting::set('first_name', 'Guest');
        UserSetting::set('last_name', 'User');
        UserSetting::set('bib', '');
        UserSetting::set('ride_id', 4);
        UserSetting::set('ride_short_name', '100k');

        $this->redirect(route('home'), navigate: true);
    }

    public function login(DeviceIdentity $deviceIdentity): void
    {
        //dd('Login Start');
        $this->reset('error');

        //$this->validate();

        //dd('Login Cont');
        $deviceInfo = $deviceIdentity->getDeviceInfo();

        // Set up a local database query to check either BIB or First/Last Name against the Registration table, and if found, log in the user. If not found, return an error message.

        // Take the input values
        //$bib = $this->bib;
        $firstName = $this->first_name;
        $lastName = $this->last_name;
        $dobDay = $this->dob_day;
        $dobMonth = $this->dob_month;
        $dobYear = $this->dob_year;

        // FIX THIS TO summarize the date of birth into a single string for comparison
        // FOR A WORKING VERSION
        $dobFull = $this->dob_full;

        //dd($dobFull, $dobDay, $dobMonth, $dobYear);

        // Check if either BIB or First/Last Name is provided and process accordingly
        if (!empty($dobFull) && !empty($firstName) && !empty($lastName)) {
            // Query the Registration table for a matching first and last name

            //dd('Checking Reg');
            $registration = Registration::where('first_name', $firstName)->where('last_name', $lastName)->first();

            //dd($registration);

            if (!$registration) {
                dd('No Reg');
                $this->error = 'No registration found for that name.';
                return;
            } else {
                //dd($registration->dob, $dobFull);

                // Name has been found, now check the date of birth against the registration record
                if ($registration->dob == $dobFull) {
                    //
                    // dd('DOB Match');
                    //$token = $registration->createToken($firstName . ' ' . $lastName)->plainTextToken;
                    //$ride_id = $this->getRideID($registration->ride);
                    $ride_id = $registration->category_entered; // Assuming 'category_entered' is the ride ID

                    $token = bcrypt($firstName . ' ' . $lastName);
                    if ($token) {
                        session(['auth_token' => $token, 'token_verified_at' => now()]);
                        UserSetting::set('Guest_User', 'false');
                        UserSetting::set('first_name', $firstName);
                        UserSetting::set('last_name', $lastName);
                        UserSetting::set('bib', $registration->bib);
                        UserSetting::set('ride_short_name', strtolower($registration->category_entered)); // Assuming 'category_entered' is the ride ID
                        //dd('Ride ID: ' . $ride_id, 'Ride Short Name: ' . $registration->category_entered);
                        $this->redirect(route('home'), navigate: true);
                        // $ride_data = Ride::where('id', $ride_id)->first();
                        // if ($ride_data) {
                        //     // If ride data is found, save the ride_id, ride_desc, and ride to UserSettings
                        //     UserSetting::set('ride_id', $ride_data->id);
                        //     UserSetting::set('ride_desc', $ride_data->ride_desc);
                        //     UserSetting::set('ride_short_name', $ride_data->ride);
                        // }
                        // return;
                    }
                } else {
                    $this->error = 'Date of birth does not match the registration record.';
                }
            }
            //$token = $registration->createToken($request->device_name)->plainTextToken;
        } else {
            // Not enough information provided, return an error message
            $token = bcrypt('FLT'); // Temporary token for testing purposes
            return;
        }

        //$registration = Registration::where('bib', $this->bib)->get();

        // try {
        //     $response = Http::api()->post('/auth/login', [
        //         'email' => $this->email,
        //         'password' => $this->password,
        //         'device_name' => $deviceInfo['model'],
        //     ]);
        //     dd($response->json());
        // } catch (\Illuminate\Http\Client\ConnectionException) {
        //     $this->error = 'Unable to connect. Please check your connection.';

        //     return;
        // }

        // If the response is successful and contains a token, store it in the session and redirect to the home page
        // if ($response->successful() && $response->json('token')) {
        //     session(['auth_token' => $response->json('token'), 'token_verified_at' => now()]);
        //     $this->redirect(route('home'), navigate: true);

        //     return;
        // }

        // If the response is not successful, set the error message to display to the user
        // OR Invalid credentials. Please try again.
        //$this->error = $response->json('message') ?? 'Invalid credentials. Please try again.';
    }
};

?>

<div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg" x-data="{ shown: false }" x-init="$nextTick(() => shown = true)">
        <div class="text-center mb-8" x-show="shown" x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <h1
                class="text-4xl font-bold bg-gradient-to-r from-green-600 to-amber-600 bg-clip-text text-transparent mb-2">
                Welcome<br>D2R2 Riders!
            </h1>
            <p class="text-base text-gray-800">Please login with your name<br> and birth date. (one time only).
                <br>Guests may use the app without<br>logging in (no messaging).
            </p>
        </div>

        <form wire:submit="login">
            <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 space-y-4"
                style="animation: fade-in-up 0.4s ease-out 0.1s both">
                @if ($error)
                    <div
                        class="rounded-xl bg-candy-50 border border-candy-200 px-4 py-3 text-sm font-medium text-candy-600 animate-wiggle">
                        {{ $error }}
                    </div>
                @endif

                {{-- <div class="space-y-1">
                    <label class="text-md font-semibold text-gray-600 pl-1">Bib Number</label>
                    <input wire:model="bib" type="text" placeholder="1234" autocomplete="bib"
                        class="w-full rounded-2xl border-2 border-white bg-white/60 px-4 py-3 text-base text-gray-700 focus:border-ocean-300 focus:outline-none transition-colors" />
                    @error('bib')
                        <p class="text-sm text-candy-600 pl-1">{{ $message }}</p>
                    @enderror
                </div> --}}

                {{-- OR block to separate out Bib and First/Last Name --}}
                {{-- <div class="flex items-center gap-3 py-1">
                    <div class="flex-1 h-px bg-gray-200"></div>
                    <span class="text-sm text-gray-400 font-medium">or</span>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div> --}}

                {{-- First and Last Name entry --}}
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-gray-600 pl-1">First Name</label>
                    <input wire:model="first_name" type="text" placeholder="John" autocomplete="given-name"
                        class="w-full rounded-2xl border-2 border-white bg-white/60 px-4 py-3 text-base text-gray-700 focus:border-ocean-300 focus:outline-none transition-colors" />
                    @error('first_name')
                        <p class="text-sm text-candy-600 pl-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-gray-600 pl-1">Last Name</label>
                    <input wire:model="last_name" type="text" placeholder="Doe" autocomplete="family-name"
                        class="w-full rounded-2xl border-2 border-white bg-white/60 px-4 py-3 text-base text-gray-700 focus:border-ocean-300 focus:outline-none transition-colors" />
                    @error('last_name')
                        <p class="text-sm text-candy-600 pl-1">{{ $message }}</p>
                    @enderror
                </div>



                <div class="space-y-1">
                    <label class="text-sm font-semibold text-gray-600 pl-1">Date of Birth (Day - Month - Year)</label>
                    <div class="flex gap-2">
                        <select wire:model="dob_month"
                            class="flex-1 rounded-2xl border-2 border-white bg-white/60 px-3 py-3 text-base text-gray-700 focus:border-ocean-300 focus:outline-none transition-colors">
                            <option value="">Month</option>
                            @for ($month = 1; $month <= 12; $month++)
                                <option value="{{ $month }}">{{ $month }}</option>
                            @endfor
                        </select>
                        <select wire:model="dob_day"
                            class="flex-1 rounded-2xl border-2 border-white bg-white/60 px-3 py-3 text-base text-gray-700 focus:border-ocean-300 focus:outline-none transition-colors">
                            <option value="">Day</option>
                            @for ($day = 1; $day <= 31; $day++)
                                <option value="{{ $day }}">{{ $day }}</option>
                            @endfor
                        </select>
                        <select wire:model="dob_year"
                            class="flex-1 rounded-2xl border-2 border-white bg-white/60 px-3 py-3 text-base text-gray-700 focus:border-ocean-300 focus:outline-none transition-colors">
                            <option value="">Year</option>
                            @for ($year = 2026; $year >= 1926; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- <div class="space-y-1">
                    <input wire:model="email" type="email" placeholder="you@example.com" autocomplete="email"
                        class="w-full rounded-2xl border-2 border-white bg-white/60 px-4 py-3 text-base text-gray-700 focus:border-ocean-300 focus:outline-none transition-colors" />
                    @error('email')
                        <p class="text-sm text-candy-600 pl-1">{{ $message }}</p>
                    @enderror
                </div> --}}

                {{-- <div class="space-y-1">
                    <label class="text-sm font-semibold text-gray-600 pl-1">Password</label>
                    <input wire:model="password" type="password" placeholder="••••••••" autocomplete="current-password"
                        class="w-full rounded-2xl border-2 border-white bg-white/60 px-4 py-3 text-base text-gray-700 focus:border-ocean-300 focus:outline-none transition-colors" />
                    @error('password')
                        <p class="text-sm text-candy-600 pl-1">{{ $message }}</p>
                    @enderror
                </div> --}}

                <div class="pt-2">
                    <button type="submit" x-data="{ pressed: false }"
                        x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                        :class="pressed ? 'scale-95' : 'scale-100'"
                        class="w-full rounded-2xl font-bold  shadow-lg shadow-ocean-200 border-gray-900 border-2 bg-green-900 min-h-[56px] text-lg text-white">
                        <span wire:loading.remove wire:target="login">Login</span>
                        <span wire:loading wire:target="login">Signing in…</span>
                    </button>
                </div>

                {{-- new class for login as a test, get rid of blend --}}
                {{-- <button type="submit" class="min-w-3/4 rounded-2xl border-gray-900 border-2 bg-green-900 px-6 py-5 text-lg font-bold text-white"> --}}

                {{-- saved class from login button --}}
                {{-- class="w-full rounded-2xl   bg-red-500 px-6 py-5 text-lg font-bold text-white shadow-lg shadow-ocean-200 hover:shadow-xl transition-all duration-200 min-h-[56px]">
 --}}
                {{-- <div class="flex items-center gap-3 py-1">
                    <div class="flex-1 h-px bg-gray-200"></div>
                    <span class="text-sm text-gray-400 font-medium">or</span>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div> --}}

                {{-- <button type="button" wire:click="loginWithGoogle" wire:loading.attr="disabled"
                    x-data="{ pressed: false }" x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                    :class="pressed ? 'scale-95' : 'scale-100'"
                    class="w-full flex items-center justify-center gap-3 rounded-2xl border-2 border-ocean-200 bg-white/80 px-6 py-4 text-base font-bold text-gray-700 hover:border-ocean-300 hover:shadow-md transition-all duration-200 min-h-[56px] disabled:opacity-60">
                    <svg wire:loading.remove wire:target="loginWithGoogle" class="w-5 h-5 shrink-0" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                            fill="#4285F4" />
                        <path
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                            fill="#34A853" />
                        <path
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"
                            fill="#FBBC05" />
                        <path
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                            fill="#EA4335" />
                    </svg>
                    <span wire:loading.remove wire:target="loginWithGoogle">Login with Google</span>
                    <span wire:loading wire:target="loginWithGoogle">Opening…</span>
                </button> --}}

                <button type="button" wire:click="guestLogin()"
                    class="w-full rounded-2xl font-bold  shadow-lg shadow-ocean-200 border-gray-900 border-2 bg-green-100 min-h-[56px] text-lg text-black">
                    Not Registered<br>Continue as a Guest</button>
            </div>


        </form>

        {{-- <p class="text-center text-sm text-gray-500 mt-6" style="animation: fade-in-up 0.4s ease-out 0.2s both">
            Use the D2R2 app as a guest.

        </p> --}}

    </div>
</div>
