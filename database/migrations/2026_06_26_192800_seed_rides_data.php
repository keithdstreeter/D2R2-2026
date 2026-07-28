<?php

use App\Models\Ride;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $this->seedRides();
    }

    public function down(): void
    {
        Ride::query()->delete();
    }

    private function seedRides(): void
    {
        $Rides = $this->loadJson('rides.json');

        if (! is_array($Rides)) {
            logger()->error('seed_rides_data: could not load rides.json');

            return;
        }

        foreach ($Rides as $RideData) {

            // dump($RideData);
            // $ageGroup = AgeGroup::query()
            //     ->where('code', $movieData['age_group_code'])
            //     ->firstOrFail();

            // $attributes = collect($dmovieData)
            //     ->except('age_group_code')
            //     ->put('age_group_id', $ageGroup->id)
            //     ->all();
            //  $movie = Movie::query()->updateOrCreate(
            //     ['slug' => $movieData['slug']],
            //     [
            //         'age_group_id' => $ageGroup->id,
            //         'title' => $movieData['title'],
            //         'release_year' => $movieData['release_year'],
            //         'poster_path' => $movieData['poster_path'],
            //         'description' => $movieData['description'],
            //         'sort_order' => $movieData['sort_order'],
            //         'is_active' => true,
            //     ],
            // );

            // $RideData = Ride::query()->updateOrCreate(
            //     [
            //         'ride' => $RideData['ride'],
            //         'turn' => $RideData['turn'],
            //         'notes' => $RideData['notes'],
            //         'distance' => $RideData['distance'],
            //     ],
            // );

            Ride::query()->updateOrCreate(
                [
                    'ride' => $RideData['ride'],
                    'ride_desc' => $RideData['ride_desc'],
                    'distance_k' => $RideData['distance_k'],
                    'distance_miles' => $RideData['distance_miles'],
                ],
            );
        }
    }

    // private function seedQuestions(): void
    // {
    //     $dataPath = database_path('data');
    //     $files = glob($dataPath.'/questions_*.json');

    //     foreach ($files as $file) {
    //         $slug = str_replace(
    //             ['questions_', '.json'],
    //             '',
    //             basename($file),
    //         );

    //         $movie = Movie::query()->where('slug', $slug)->first();

    //         if (! $movie) {
    //             continue;
    //         }
    //         $questions = json_decode(file_get_contents($file), true);

    //         foreach ($questions as $questionData) {
    //             $choices = $questionData['choices'];
    //             unset($questionData['choices']);

    //             $question = Question::query()->updateOrCreate(
    //                 [
    //                     'movie_id' => $movie->id,
    //                     'prompt' => $questionData['prompt'],
    //                 ],
    //                 array_merge($questionData, ['movie_id' => $movie->id]),
    //             );

    //             foreach ($choices as $index => $choiceData) {
    //                 $question->choices()->updateOrCreate(
    //                     ['label' => $choiceData['label']],
    //                     array_merge($choiceData, ['sort_order' => $index + 1]),
    //                 );
    //             }
    //         }
    //     }
    // }

    /** @return array<int, array<string, mixed>> */
    private function loadJson(string $filename): array
    {
        $path = database_path('data/'.$filename);

        return json_decode(file_get_contents($path), true);
    }
};
