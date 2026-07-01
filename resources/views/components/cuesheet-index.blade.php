<?php

use App\Models\Cuesheet;
use App\Models\Ride;
use App\Models\QuizSession;
use App\Models\UserSetting;
use App\Services\ContentSync;
use App\Services\NetworkStatus;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Your Ride Cuesheet')] class extends Component
{
    public ?int $ageGroupId = null;

    public function mount(): void
    {
        //$this->ageGroupId = (int) UserSetting::get('age_group_id') ?: null;

        // if (! app()->runningUnitTests() && app(NetworkStatus::class)->isOnline()) {
        //     app(ContentSync::class)->sync();
        // }

        UserSetting::set('last_content_viewed', now()->toIso8601String());
    }

    #[Computed]
    public function hasNewContent(): bool
    {
        //return app(ContentSync::class)->hasNewContent();
    }

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

        $query = Cuesheet::all()->where('ride', '180k');

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

{{-- Standard Screen Mobile --}}
<div class="min-h-screen px-4 py-8">
    <div class="mx-auto max-w-lg">
        {{-- Title and Back Button --}}
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Partial Cue Sheet</h1>
            <a href="{{ route('home') }}" wire:navigate class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">&larr; Back</a>
        </div>

        {{-- Cue Sheet --}}
        <div class="relative overflow-hidden shadow-md ">
            <table class="table-auto w-full text-left">
                {{-- Table Header --}}
                <thead class="uppercase bg-[#2455b6] text-[#ffffff]" style="background-color: rgb(36, 85, 182); color: rgb(255, 255, 255);">
                    <tr>
                        <!--[-->
                        <td class="py-1  text-center font-bold p-4">Turn</td>
                        <td class="py-1  text-center font-bold p-4">Description</td>
                        <td class="py-1  text-center font-bold p-4">Distance</td>
                        <!--[-->
                    </tr>
                </thead>

                {{-- Table Body --}}
                <tbody class="bg-white text-gray-500 bg-[#000000] text-[#ececec]" style="background-color: rgb(0, 0, 0); color: rgb(236, 236, 236);;">
                    {{-- Loop through cuesheet data and display in table --}}
                    @foreach ($this->cuesheets as $index => $cuesheet)
                        {{-- Alternate row colors --}}
                        @if($loop->iteration % 2 == 0)
                            <tr class=" py-2" style="">
                                <td class=" py-2 border  border-gray-300   p-4">{{ $cuesheet->turn }}</td>
                                <td class=" py-2 border  border-gray-300   p-4">{{ $cuesheet->Notes }}</td>
                                <td class=" py-2 border  border-gray-300   p-4">{{ $cuesheet->distance }}</td>
                            </tr>
                        @else
                            <tr class=" py-2" style="background-color: rgb(18, 79, 141); color: rgb(243, 243, 243);">
                                <td class=" py-2 border  border-gray-300   p-4">{{ $cuesheet->turn }}</td>
                                <td class=" py-2 border  border-gray-300   p-4">{{ $cuesheet->Notes }}</td>
                                <td class=" py-2 border  border-gray-300   p-4">{{ $cuesheet->distance }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

