<?php

use App\Models\Cuesheet;
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
        $query = Cuesheet::all();

        //$query = Movie::active()->orderBy('sort_order');
        // if ($this->ageGroupId) {
        //     $query->where('age_group_id', $this->ageGroupId);
        // }

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

<div class="min-h-screen px-4 py-8">
    <div class="mx-auto max-w-lg">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Partial Cue Sheet</h1>
            <a href="{{ route('home') }}" wire:navigate class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">&larr; Back</a>
        </div>

        {{-- @if ($this->hasNewContent)
            <div class="mb-4 flex items-center justify-between rounded-2xl bg-mint-50 border-2 border-mint-200 p-4 animate-fade-in">
                <p class="text-sm font-medium text-mint-700">New content available!</p>
                <button wire:click="dismissNewContent" class="text-xs font-medium text-mint-500 hover:text-mint-700 transition-colors">Dismiss</button>
            </div>
        @endif --}}

        {{-- @if ($this->movies->isEmpty())
            <div class="rounded-2xl bg-white/80 backdrop-blur-sm p-10 text-center animate-fade-in">
                <p class="text-lg text-gray-500">No movies found. Please select an age group first.</p>
            </div>
        @endif --}}

        {{-- <div class="grid gap-4"> --}}


        <div class="relative overflow-hidden shadow-md ">
            <table class="table-auto w-full text-left">
                <thead class="uppercase bg-[#2455b6] text-[#ffffff]" style="background-color: rgb(36, 85, 182); color: rgb(255, 255, 255);">
                    <tr>
                        <!--[-->
                        <td class="py-1  text-center font-bold p-4">Turn</td>
                        <td class="py-1  text-center font-bold p-4">Description</td>
                        <td class="py-1  text-center font-bold p-4">Distance</td>
                        <!--]-->
                    </tr>
                </thead>
                <tbody class="bg-white text-gray-500 bg-[#000000] text-[#ececec]" style="background-color: rgb(0, 0, 0); color: rgb(236, 236, 236);">
                    <!--[-->
            @foreach ($this->cuesheets as $index => $cuesheet)


            @if($loop->iteration % 2 == 0)
        <!--[-->
            <tr class=" py-2" style="">
                <!--[-->
                <td class=" py-2 border  border-gray-300   p-4">{{ $cuesheet->turn }}</td>
                <td class=" py-2 border  border-gray-300   p-4">{{ $cuesheet->notes }}</td>
                <td class=" py-2 border  border-gray-300   p-4">{{ $cuesheet->distance }}</td>
                <!--]-->
            </tr>
    @else
        <tr class=" py-2" style="background-color: rgb(18, 79, 141); color: rgb(243, 243, 243);">
                <!--[-->
                <td class=" py-2 border  border-gray-300   p-4">{{ $cuesheet->turn }}</td>
                <td class=" py-2 border  border-gray-300   p-4">{{ $cuesheet->notes }}</td>
                <td class=" py-2 border  border-gray-300   p-4">{{ $cuesheet->distance }}</td>
                <!--]-->
            </tr>
    @endif
             
            
                {{-- <a
                    href="{{ route('movies.show', $movie->slug) }}"
                    wire:key="movie-{{ $movie->id }}"
                    wire:navigate
                    x-data="{ pressed: false }"
                    x-on:mousedown="pressed = true"
                    x-on:mouseup="pressed = false"
                    x-on:mouseleave="pressed = false"
                    x-on:touchstart="pressed = true"
                    x-on:touchend="pressed = false"
                    :class="pressed ? 'scale-[0.98]' : 'scale-100'"
                    class="block rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-5 transition-all duration-200 hover:border-ocean-300 hover:shadow-lg"
                    style="animation: fade-in-up 0.4s ease-out {{ $index * 0.06 }}s both"
                > --}}
                    {{-- <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">{{ $cuesheet->turn }}</h2>
                            <p class="mt-1 text-sm text-gray-500">{{ $cuesheet->notes }} &middot; {{ $cuesheet->distance }} questions</p>
                        </div> --}}

                        {{-- @if ($this->stats->has($movie->id))
                            <div class="text-right text-sm">
                                <div class="font-bold text-mint-600">{{ round($this->stats[$cuesheet->id]->best_score) }}%</div>
                                <div class="text-gray-400">{{ $this->stats[$cuesheet->id]->attempts }} {{ Str::plural('attempt', $this->stats[$movie->id]->attempts) }}</div>
                            </div>
                        @else
                            <span class="rounded-full bg-ocean-100 px-3 py-1 text-xs font-medium text-ocean-500">New</span>
                        @endif --}}
                    {{-- </div> --}}

                    {{-- @if ($this->stats->has($movie->id))
                        <div class="mt-3">
                            <div class="h-2 rounded-full bg-gray-100">
                                <div
                                    class="h-2 rounded-full bg-gradient-to-r from-mint-400 to-mint-500 transition-all duration-500"
                                    style="width: {{ round($this->stats[$movie->id]->best_score) }}%"
                                ></div>
                            </div>
                            <p class="mt-1.5 text-xs text-gray-400">
                                Last played {{ \Carbon\Carbon::parse($this->stats[$movie->id]->last_played)->diffForHumans() }}
                            </p>
                        </div>
                    @endif --}}
                {{-- </a> --}}
            @endforeach

              </tbody>
    </table>
</div>
        </div>
    </div>
</div>
