<?php

use App\Models\Cuesheet;
use App\Models\Ride;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $this->seedCuesheet();
    }

    public function down(): void
    {
        // Ride::query()->delete();
        Cuesheet::query()->delete();
    }

    private function seedCuesheet(): void
    {

        // New - load all rides from table and then load each ride json file and insert into cuesheet table
        // $query = Ride::all();
        $newCount = 0;

        try {

            $filename = 'cuesheets.json';
            $data = $this->loadJson($filename);

            foreach ($data as $cuesheetEntry) {
                $newCuesheetEntry = [
                    'ride' => $cuesheetEntry['ride'],
                    'turn' => $cuesheetEntry['turn'],
                    'notes' => $cuesheetEntry['notes'],
                    'distance' => $cuesheetEntry['distance'],
                    'completed' => 0,
                ];
                $newCount++;
                // Create each cuesheet entry in the database
                Cuesheet::create($newCuesheetEntry);
            }
        } catch (Throwable $e) {
            Log::error('Failed to retrieve rides from json: '.$e->getMessage());

            return;
        }

        Log::info('Cuesheet sync completed. New entries inserted: '.$newCount);

        // foreach ($query as $ride) {
        //     try {
        //         $filename = 'cuesheet_'.$ride->ride.'.json';
        //         $Cuesheet = $this->loadJson($filename);
        //         //dump($filename);
        //         foreach ($Cuesheet as $RideData) {

        //             // Handle ride names from the JSON file that may not match the ride name in the database

        //             //dd('Short Name: ' . $rideShortName, 'Ride Data Ride: ' . $RideData['ride']);
        //             // $rideShortName = $RideData['ride'];  // This line is no longer needed
        //             $rideShortName = $RideData['ride'];

        //             Cuesheet::query()->updateOrCreate(
        //                 [
        //                     'ride' => strtolower($rideShortName),
        //                     'turn' => $RideData['turn'],
        //                     'notes' => $RideData['notes'],
        //                     'distance' => $RideData['distance'],
        //                     'completed' => 0,
        //                 ],
        //             );
        //         }
        //     } catch (\Throwable $e) {
        //         Log::error('Failed to load cuesheet JSON for ride '.$ride->ride.': '.$e->getMessage());
        //         continue; // Skip to the next ride if there's an error
        //     }
        // }

    }

    /** @return array<int, array<string, mixed>> */
    private function loadJson(string $filename): array
    {
        $path = $this->resolveJsonPath($filename);

        if ($path === null) {
            Log::error('seed_cuesheets_data: seed file missing', [
                'filename' => $filename,
            ]);

            return [];
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            Log::error('seed_cuesheets_data: unable to read seed file', [
                'path' => $path,
            ]);

            return [];
        }

        $decoded = json_decode($contents, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function resolveJsonPath(string $filename): ?string
    {
        $expectedPath = database_path('data/'.$filename);

        if (is_file($expectedPath)) {
            return $expectedPath;
        }

        $normalizedFilename = strtolower($filename);

        foreach (glob(database_path('data/*.json')) as $path) {
            if (strtolower(basename($path)) === $normalizedFilename) {
                return $path;
            }
        }

        return null;
    }
};
