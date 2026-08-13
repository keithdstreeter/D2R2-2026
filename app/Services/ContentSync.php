<?php

namespace App\Services;

use App\Models\AgeGroup;
use App\Models\Cuesheet;
use App\Models\Movie;
use App\Models\Question;
use App\Models\Registration;
use App\Models\UserSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContentSync
{
    public function __construct(
        protected NetworkStatus $networkStatus,
    ) {}

    public function sync(): int
    {

        // dd('ContentSync::sync() called');

        Log::info('ContentSync::sync() called at '.now()->toDateTimeString());
        if (! $this->networkStatus->isOnline()) {
            return 0;
        }

        $lastSync = UserSetting::get('last_content_sync');
        Log::info('Last content sync timestamp: '.($lastSync ?? 'never'));

        // ##############################
        // Cuesheet Sync
        // ##############################
        // $data = $this->fetchCuesheetData($lastSync);
        // if ($data === null) {

        //     Log::warning('Cue Sheet sync not performed due to last sync timestamp being too old or no new content available.');
        //     //dd(' Failed to fetch cuesheet data. Last sync: '.$lastSync);
        //     return 0;
        // }

        $newCount = 0;

        try {
            $baseUrl = config('services.api.url');
            $url = $baseUrl.'/auth/cuesheets';
            $query = [];
            $response = Http::timeout(10)
                ->acceptJson()
                ->get($url, $query);
            $data = $response->json();
        } catch (\Throwable $e) {
            Log::error('Failed to retrieve cuesheet data from API: '.$e->getMessage());

            return 0;
        }

        // $baseUrl = config('services.api.url');
        // $url = $baseUrl.'/auth/cuesheets';
        // $query = [];
        // $response = Http::timeout(10)
        //         ->acceptJson()
        //         ->get($url, $query);
        // $data = $response->json();

        if ($data === null) {
            Log::warning('Cuesheet sync not performed due to last sync timestamp being too old or no new content available.');

            return 0;
        } else {
            // Clear existing cuesheet entries before inserting new ones
            Log::info('Truncating Cuesheet table before inserting new entries.');
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
                $newCount++;
                // Create each cuesheet entry in the database
                Cuesheet::create($newCuesheetEntry);
            }
            Log::info('Cuesheet sync completed. New entries inserted: '.$newCount);
        }

        // ##############################
        // Registration Sync
        // ##############################
        // $data = $this->fetchRegistrationData($lastSync);
        // if ($data === null) {
        //     Log::warning('Registration sync not performed due to last sync timestamp being too old or no new content available.');
        //     return 0;
        // }

        $newCount = 0;

        try {
            $baseUrl = config('services.api.url');
            $url = $baseUrl.'/auth/registrations';
            $query = [];
            $response = Http::timeout(10)
                ->acceptJson()
                ->get($url, $query);
            $data = $response->json();
        } catch (\Throwable $e) {
            Log::error('Failed to retrieve cuesheet data from API: '.$e->getMessage());

            return 0;
        }

        if ($data === null) {
            Log::warning('Registration sync not performed due to last sync timestamp being too old or no new content available.');

            return 0;
        } else {
            Log::info('Truncating Registration table before inserting new entries.');
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

                $newCount++;

                // Create each registration entry in the database
                Registration::create($newRegistrationEntry);
            }
            Log::info('Registration sync completed. New entries inserted: '.$newCount);

            UserSetting::set('last_content_sync', now()->toDateTimeString());

            return $newCount;
        }
    }

    public function hasNewContent(): bool
    {
        return ((int) (UserSetting::get('new_content_count') ?? 0)) > 0;
    }

    public function clearNewContentFlag(): void
    {
        UserSetting::set('new_content_count', '0');
    }

    /** @return array<int, array<string, mixed>>|null */
    protected function fetchCuesheetData(?string $since): ?array
    {
        return $this->fetchResourceData('/auth/cuesheets', 'cuesheets');
    }

    /** @return array<int, array<string, mixed>>|null */
    protected function fetchRegistrationData(?string $since): ?array
    {
        return $this->fetchResourceData('/auth/registrations', 'registrations');
    }

    /** @return array<int, array<string, mixed>>|null */
    protected function fetchResourceData(string $endpoint, string $resource): ?array
    {
        $baseUrl = $this->resolveApiBaseUrl($resource);
        if ($baseUrl === null) {
            return null;
        }

        $url = $baseUrl.$endpoint;

        try {
            $token = config('services.external_api.token');
            $response = Http::timeout(10)
                ->acceptJson()
                ->withToken($token)
                ->get($url);

            if (! $response->successful()) {
                Log::warning('Content sync request failed', [
                    'resource' => $resource,
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $payload = $response->json();

            if (! is_array($payload)) {
                Log::warning('Content sync returned non-array payload', [
                    'resource' => $resource,
                    'url' => $url,
                    'status' => $response->status(),
                    'payload_type' => gettype($payload),
                ]);

                return null;
            }

            return $payload;
        } catch (ConnectionException $e) {
            Log::warning('Content sync connection failed', [
                'resource' => $resource,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function resolveApiBaseUrl(string $resource): ?string
    {
        $baseUrl = rtrim((string) config('services.api.url', ''), '/');

        if ($baseUrl === '') {
            Log::warning('Content sync skipped because API_BASE_URL is missing', [
                'resource' => $resource,
            ]);

            return null;
        }

        $host = parse_url($baseUrl, PHP_URL_HOST);
        $scheme = parse_url($baseUrl, PHP_URL_SCHEME);

        if (! is_string($host) || $host === '' || ! is_string($scheme) || $scheme === '') {
            Log::warning('Content sync skipped because API_BASE_URL is invalid', [
                'resource' => $resource,
                'api_base_url' => $baseUrl,
            ]);

            return null;
        }

        // if (in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true) || str_ends_with(strtolower($host), '.test')) {
        //     Log::warning('Content sync API host is likely unreachable from a physical device', [
        //         'resource' => $resource,
        //         'api_base_url' => $baseUrl,
        //         'host' => $host,
        //     ]);
        // }

        return $baseUrl;
    }

    /** @return array<string, mixed>|null */
    protected function fetchQuestions(?string $since): ?array
    {
        try {
            // Http::baseUrl(config('services.api.url'))
            // $baseUrl = config('app.url');
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
