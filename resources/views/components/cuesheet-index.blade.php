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

    public function mount(): void
    {
        //$this->ageGroupId = (int) UserSetting::get('age_group_id') ?: null;

        //$this->updateRideIds = [];

        $this->ride_id = UserSetting::get('ride_id') ?: null;

        //dd($this->ride_id);
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
                        'ride' => $this->ride_id,
                        'id' => $ID,
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

        $current_ride_id = UserSetting::get('ride_id') ?: null;
//$flight->updateOrFail(['name' => 'Paris to London']);
        foreach ($this->updateRideIds as $checkedID) {

             DB::table('cuesheets')
                ->where(['id', $checkedID, 'ride', $current_ride_id])
                ->update(['completed' => 1]);

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

    public function updateCompleted( $ID, $ride_id) 
    {
        dd("clicked", $ID, $ride_id);   
        Cuesheet::query()->updateOrCreate(
            [
                'ride' => $ID,
                'id' => $ID,
            ],
            ['completed' => 1],
        );

        dd($ID, $ride_id);
    }

     public function updated($name, $value) 
    {
        // Use the Livewire updated lifecycle hook to save the updated value
        // to the Cuesheet model
        // The $name parameter will contain the Cuesheet Model item
        // The $value parameter will contain the selected value from that item (checkbox)

        //dd('UPDATED',$name, $value);

        // Need to pull the Ride again, since it is passed into this routine 
        // which is firing but not by design. Can't control it so going with 
        // in to see if I can make the Back button work.

        $current_ride_id = UserSetting::get('ride_id') ?: null;

foreach ($value as $checkedID) {
            Cuesheet::query()->updateOrCreate(
                [
                    'ride' => $current_ride_id,
                    'id' => $checkedID,
                ],
                ['completed' => 1],
            );
        }
        $this->redirect(route('home'));

        //dd('KDS updated', $name, $value, $updateRideIds);
        //dd($name, $value);
        // $this->post->update([
        //     $name => $value,
        // ]);
    }

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
        //$query = Cuesheet::ride()->orderBy('id');

        $query = Cuesheet::all()->where('ride', $this->ride_id);

        //dump($query->count());
        //$query = Movie::active()->orderBy('sort_order');
        // if ($this->ageGroupId) {
        //     $query->where('age_group_id', $this->ageGroupId);
        // }

        //dd($query);

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
    <div class="min-h-screen px-4 py-8">

        <div class="mx-auto max-w-lg mt- 20">
            {{-- Title and Back Button --}}
            <div class="mb-6 px-4 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-800">{{ $this->ride_id }} Cue Sheet</h1>
                {{-- {{ implode(', ', $this->updateRideIds) }} --}}

                {{-- <button type="button" wire:click="showVals($updateRideIds)">
                Show
            </button> --}}

                {{-- <a href="{{ route('home') }}" wire:navigate
                class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">&larr; Back</a> --}}
                <button type="button" wire:click="saveChecks()">Back</button>

                {{-- <button type="submit">Complete</button> --}}

                {{-- <a href="#" wire:click.prevent="saveChecks($updateRideIds)">GO HOME</a> --}}
            </div>
        </div>
        <div
            class="relative
                    overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
            <table class="table-fixed w-full text-sm text-left rtl:text-right text-fg-brand-subtle">
                <thead class="text-sm text-white bg-brand-strong">
                    <tr>
                        <th scope="col" class="max-w-[12px] text-center font-medium">
                            Done
                        </th>
                        <th scope="col" class="px-6 py-3 font-medium">
                            Turn
                        </th>
                        <th scope="col" class="px-6 py-3 font-medium">
                            Notes
                        </th>
                        <th scope="col" class="px-6 py-3 font-medium">
                            Dist
                        </th>

                    </tr>
                </thead>
                <tbody>
                    {{-- <tr> --}}
                    @foreach ($this->cuesheets as $index => $cuesheet)
                        {{-- Alternate row colors --}}
                        @if ($loop->iteration % 2 == 0)
                            <tr class=bg-brand border-b border-brand-light">
                                {{-- <div wire:key="{{ $post->id }}"> --}}
                                <td class=""max-w-[20px] align-center px-6 py-4 font-medium text-fg-brand-subtle
                                    whitespace-nowrap">
                                    &nbsp; &nbsp;
                                    {{-- wire:model.live="ride" --}}

                                    <input type="checkbox" value="{{ $cuesheet->id }}" wire:model="updateRideIds">

                                    {{-- <input wire:click="update({{ $cuesheet->id }}, {{ $this->ride_id }})" id="default-checkbox"
                            type="checkbox"
                            class="w-4 h-4 border  border-default-medium rounded-xs bg-neutral-secondary-medium focus:ring-2 focus:ring-brand-soft"> --}}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $cuesheet->turn }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $cuesheet->notes }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $cuesheet->distance }}
                                </td>

                            </tr>
                        @else
                            <tr class="bg-brand-strong border-b border-brand-light">
                                <td class=""max-w-[20px] text-right px-6 py-4 font-medium text-fg-brand-subtle
                                    whitespace-nowrap">
                                    &nbsp; &nbsp;
                                    {{-- <input checked id="default-checkbox" type="checkbox" value=""
                            class="w-4 h-4 border border-default-medium rounded-xs bg-neutral-secondary-medium focus:ring-2 focus:ring-brand-soft"> --}}

                                    <input wire:model="updateRideIds" value="{{ $cuesheet->id }}" id="default-checkbox"
                                        type="checkbox"
                                        class="w-4 h-4 border  border-default-medium rounded-xs bg-neutral-secondary-medium focus:ring-2 focus:ring-brand-soft">

                                </td>
                                <td class="px-6 py-4">
                                    {{ $cuesheet->turn }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $cuesheet->notes }}
                                </td>
                                <td class="px-6 py-4">
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
