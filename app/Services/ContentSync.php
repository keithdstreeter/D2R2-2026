<?php

namespace App\Services;

use App\Models\Cuesheet;
use App\Models\Question;
use App\Models\Movie;
use App\Models\AgeGroup;
use App\Models\Registration;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContentSync
{
    public function __construct(
        protected NetworkStatus $networkStatus,
    ) {}

    public function sync(): int
    {

        if (! $this->networkStatus->isOnline()) {
            return 0;
        }

        $lastSync = UserSetting::get('last_content_sync');


        // ##############################
        // Cuesheet Sync
        // ##############################
        $data = $this->fetchCuesheetData($lastSync);
        if ($data === null) {
            Log::warning('Content sync not performed due to last sync timestamp being too old or no new content available.');
            return 0;
        }

        $newCount = 0;

        // Clear existing cuesheet entries before inserting new ones
        Cuesheet::truncate();
        // Read all cuesheet entries from the fetched data and insert them into the database
        foreach ($data as $cuesheetEntry) {
            $newCuesheetEntry = [
                'ride' => $cuesheetEntry['ride'],
                'turn' => $cuesheetEntry['turn'],
                'notes' => $cuesheetEntry['notes'],
                'distance' => $cuesheetEntry['distance'],
                'completed' => $cuesheetEntry['completed'],
            ];

            // Create each cuesheet entry in the database
            Cuesheet::create($newCuesheetEntry); 
        }


        // ##############################
        // Registration Sync
        // ##############################
        $data = $this->fetchRegistrationData($lastSync);
        if ($data === null) {
            Log::warning('Content sync not performed due to last sync timestamp being too old or no new content available.');
            return 0;
        }

        $newCount = 0;

        // Clear existing registration entries before inserting new ones
        Registration::truncate();
        // Read all registration entries from the fetched data and insert them into the database
        foreach ($data as $registrationEntry) {
            $newRegistrationEntry = [
                'bib' => $registrationEntry['bib'],
                'first_name' => $registrationEntry['first_name'],
                'last_name' => $registrationEntry['last_name'],
                'phone' => $registrationEntry['phone'],
                'category_entered' => $registrationEntry['category_entered'],
                'email' => $registrationEntry['email'],
                'dob' => $registrationEntry['dob'],
                'gender' => $registrationEntry['gender'],
                // 'created_at' => $registrationEntry['created_at'],
                // 'updated_at' => $registrationEntry['updated_at'],
            ];
	

            // Create each registration entry in the database
            Registration::create($newRegistrationEntry); 
        }

        UserSetting::set('last_content_sync', now()->toDateTimeString());

        return $newCount;
    }

    public function hasNewContent(): bool
    {
        return ((int) (UserSetting::get('new_content_count') ?? 0)) > 0;
    }

    public function clearNewContentFlag(): void
    {
        UserSetting::set('new_content_count', '0');
    }

     /** @return array<string, mixed>|null */
    protected function fetchCuesheetData(?string $since): ?array
    {
        try {
            $baseUrl = config('services.api.url');
            $url = $baseUrl.'/auth/cuesheets';

            $query = [];
            
            $response = Http::timeout(10)
                ->acceptJson()
                ->get($url, $query);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Content sync failed - Cuesheets', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }   

    /** @return array<string, mixed>|null */
    protected function fetchRegistrationData(?string $since): ?array
    {
        try {
            $baseUrl = config('services.api.url');
            $url = $baseUrl.'/auth/registrations';

            $query = [];
            
            $response = Http::timeout(10)
                ->acceptJson()
                ->get($url, $query);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Content sync failed - Registrations', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }   

    /** @return array<string, mixed>|null */
    protected function fetchQuestions(?string $since): ?array
    {
        try {
            //Http::baseUrl(config('services.api.url'))
            //$baseUrl = config('app.url');
            $baseUrl = config('services.api.url');

            $url = $baseUrl.'/api/questions';

            $query = [];
            if ($since) {
                $query['since'] = $since;
            }

            $response = Http::timeout(10)
                ->acceptJson()
                ->get($url, $query);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Content sync failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /** @param array<string, mixed> $movieData */
    protected function upsertMovie(array $movieData): Movie
    {
        $ageGroup = AgeGroup::query()
            ->where('code', $movieData['age_group_code'])
            ->firstOrFail();

        return Movie::query()->updateOrCreate(
            ['slug' => $movieData['slug']],
            [
                'title' => $movieData['title'],
                'age_group_id' => $ageGroup->id,
                'release_year' => $movieData['release_year'],
                'poster_path' => $movieData['poster_path'],
                'description' => $movieData['description'],
                'sort_order' => $movieData['sort_order'],
                'is_active' => true,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $questionData
     */
    protected function upsertQuestion(Movie $movie, array $questionData): bool
    {
        $question = Question::query()->updateOrCreate(
            [
                'movie_id' => $movie->id,
                'prompt' => $questionData['prompt'],
            ],
            [
                'difficulty' => $questionData['difficulty'],
                'kind' => $questionData['kind'],
                'explanation' => $questionData['explanation'],
                'is_active' => true,
            ],
        );

        foreach ($questionData['choices'] as $index => $choiceData) {
            $question->choices()->updateOrCreate(
                ['label' => $choiceData['label']],
                [
                    'text' => $choiceData['text'],
                    'is_correct' => $choiceData['is_correct'],
                    'sort_order' => $index + 1,
                ],
            );
        }

        return $question->wasRecentlyCreated;
    }
}
