<?php

use App\Models\Cuesheet;
use App\Models\Ride;
use App\Models\QuizSession;
use App\Models\UserSetting;
use App\Services\ContentSync;
use App\Services\NetworkStatus;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Your Ride Cuesheet')] class extends Component
{
    public ?int $ageGroupId = null;

    public  $updateRideIds = [];
    public ?string $rideShortName = null;

    public function mount(): void
    {
        $this->rideShortName = UserSetting::get('ride_short_name') ?: null;

        // Load any COMPLETED cuesheet items for this ride into the updateRideIds array
        // May be empty if none have been completed yet, but will be used to pre-check the checkboxes in the view
        $this->updateRideIds = Cuesheet::where('ride', $this->rideShortName)
            ->where('completed', 1)
            ->pluck('id')
            ->toArray();

            //dd($this->rideShortName, $this->updateRideIds);
        //dd($this->rideShortName);
        // if (! app()->runningUnitTests() && app(NetworkStatus::class)->isOnline()) {
        //     app(ContentSync::class)->sync();
        // }

        UserSetting::set('last_content_viewed', now()->toIso8601String());
    }

    // public function showVals($updateRideIds)
    // {
    //     // dd($updateRideIds);
    // }
    
     public function save(array $updateRideIds)
    {
       //dd('Save Checks');

       if (! $updateRideIds) {
            foreach ($updateRideIds as $checkedID) {
                Cuesheet::query()->updateOrCreate(
                    [
                        'ride' => $this->rideShortName,
                        'id' => $checkedID,
                    ],
                    ['completed' => 1],
                );
            }
        }
        
        $this->redirect(route('home'));
    }

    public function saveChecks()
    {
        // if (! $updateRideIds) {
        //     return;
        // }

        //dd('Save Checks', $this->updateRideIds);

        $current_ride_id = $this->rideShortName ?: null;
//$flight->updateOrFail(['name' => 'Paris to London']);
        foreach ($this->updateRideIds as $checkedID) {

            Cuesheet::query()->updateOrCreate(
                [
                    'ride' => $current_ride_id,
                    'id' => $checkedID,
                ],
                ['completed' => 1],
            );

//             $query = Cuesheet::find([
//                 'ride' => $current_ride_id,
//                 'id' => $checkedID,
//             ]);
 
// $query->completed = 1;
 
// $query->save();
            // Cuesheet::query()->updateOrFail(
            //     [
            //         'ride' => $current_ride_id,
            //         'id' => $checkedID,
            //     ],
            //     ['completed' => 1],
            // );
            //dd('Saved', $checkedID, $current_ride_id);
        }
        $this->redirect(route('home'));
    }

    // public function updateCompleted( $ID, $ride_id) 
    // {
    //     dd("clicked", $ID, $ride_id);   
    //     Cuesheet::query()->updateOrCreate(
    //         [
    //             'ride' => $ID,
    //             'id' => $ID,
    //         ],
    //         ['completed' => 1],
    //     );

    //     dd($ID, $ride_id);
    // }

//      public function updated($name, $value) 
//     {
//         // Use the Livewire updated lifecycle hook to save the updated value
//         // to the Cuesheet model
//         // The $name parameter will contain the Cuesheet Model item
//         // The $value parameter will contain the selected value from that item (checkbox)

//         //dd('UPDATED',$name, $value);

//         // Need to pull the Ride again, since it is passed into this routine 
//         // which is firing but not by design. Can't control it so going with 
//         // in to see if I can make the Back button work.

//         $current_ride_id = UserSetting::get('ride_short_name') ?: null;

// foreach ($value as $checkedID) {
//             Cuesheet::query()->updateOrCreate(
//                 [
//                     'ride' => $current_ride_id,
//                     'id' => $checkedID,
//                 ],
//                 ['completed' => 1],
//             );
//         }
//         $this->redirect(route('home'));

//         //dd('KDS updated', $name, $value, $updateRideIds);
//         //dd($name, $value);
//         // $this->post->update([
//         //     $name => $value,
//         // ]);
//     }

    #[Computed]
    // public function hasNewContent(): bool
    // {
    //     //return app(ContentSync::class)->hasNewContent();
    // }

    public function dismissNewContent(): void
    {
        app(ContentSync::class)->clearNewContentFlag();
        unset($this->hasNewContent);
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Cuesheet> */
    #[Computed]
    public function cuesheets(): \Illuminate\Database\Eloquent\Collection
    {

        $query = Cuesheet::all()->where('ride', $this->rideShortName);

        return $query;
    }

    /** @return \Illuminate\Support\Collection<int, object> */
    #[Computed]
    // public function stats(): \Illuminate\Support\Collection
    // {
    //     //$movieIds = $this->movies->pluck('id');

    //     return Cuesheet::query()
    //         ->whereIn('ride', '180k')
    //         // ->whereNotNull('completed_at')
    //         // ->selectRaw('movie_id, count(*) as attempts, max(correct_count * 100 / question_count) as best_score, max(completed_at) as last_played')
    //         // ->groupBy('movie_id')
    //         ->get()
    //         ->keyBy('id');
    // }
};
?>

<form wire:submit="save()">
    <div class="min-h-screen px-1 py-2">
        <div class="mx-auto max-w-lg mt-16">
            {{-- Title and Back Button --}}
            <div class="mb-6 px-4 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-800">{{ $this->rideShortName }} Cue Sheet</h1>
                {{-- {{ implode(', ', $this->updateRideIds) }} --}}

                {{-- <button type="button" wire:click="showVals($updateRideIds)">
                Show
            </button> --}}

                {{-- <a href="{{ route('home') }}" wire:navigate
                class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">&larr; Back</a> --}}
                <button type="button" wire:click="saveChecks()">&larr; Back</button>

            </div>
        </div>

        <div class="w-full overflow-x-auto rounded-md border border-gray-200 shadow-sm">
            <table class="w-full table-fixed divide-y divide-gray-200 text-left text-sm text-gray-700">
                <thead class="bg-slate-900 text-xs font-semibold uppercase tracking-wider text-gray-100">
                    <tr>
                        <th scope="col" class="w-[8%] px-1 py-3">Done</th>
                        <th scope="col" class="w-[15%] px-4 py-3">Turn</th>
                        <th scope="col" class="w-[65%] px-4 py-3">Notes</th>
                        <th scope="col" class="w-[12%] px-4 py-3 text-right">Dist</th>
                    </tr>
                </thead>

                @foreach ($this->cuesheets as $index => $cuesheet)
                    @if ($loop->iteration % 2 == 0)
                        <tr class="bg-brand border-b border-gray-200 bg-slate-800">
                            <td class="px-4 py-3 font-medium text-white">
                                <input id="checked-checkbox" type="checkbox" value="{{ $cuesheet->id }}"
                                    wire:model="updateRideIds"
                                    class="accent-black w-4 h-4 border border-default-medium rounded-xs  focus:ring-2 focus:ring-brand-soft">
                            </td>
                            <td class="px-4 py-3 text-white">
                                {{ $cuesheet->turn }}
                            </td>
                            <td class="px-4 py-3 text-white">
                                {{ $cuesheet->notes }}
                            </td>
                            <td class="px-4 py-3 text-white text-right tabular-nums">
                                {{ $cuesheet->distance }}
                            </td>
                        </tr>
                    @else
                        <tr class="bg-brand-strong border-b border-gray-200 bg-green-900">
                            <td class="px-4 py-3 font-medium text-white">
                                <input wire:model="updateRideIds" value="{{ $cuesheet->id }}" id="checked-checkbox"
                                    type="checkbox"
                                    class="accent-black w-4 h-4 border align-middle border-default-medium rounded-xs  focus:ring-2 focus:ring-brand-soft">
                            </td>
                            <td class="px-4 py-3 text-white">
                                {{ $cuesheet->turn }}
                            </td>
                            <td class="px-4 py-3 text-white">
                                {{ $cuesheet->notes }}
                            </td>
                            <td class="px-4 py-3 text-white text-right tabular-nums">
                                {{ $cuesheet->distance }}
                            </td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</form>
