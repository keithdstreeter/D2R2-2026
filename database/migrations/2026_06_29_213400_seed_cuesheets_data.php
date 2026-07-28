<?php

use App\Models\Ride;
use App\Models\Cuesheet;
use Illuminate\Database\Migrations\Migration;

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
        Ride::query()->delete();
    }

    private function seedCuesheet(): void
    {

        // New - load all rides from table and then load each ride json file and insert into cuesheet table
        $query = Ride::all();
        
        foreach ($query as $ride) {
            $filename = 'cuesheet_'.$ride->ride.'.json';
            $Cuesheet = $this->loadJson($filename);
            //dump($filename);
            foreach ($Cuesheet as $RideData) {
                Cuesheet::query()->updateOrCreate(
                    [
                        'ride' => $RideData['ride'],
                        'turn' => $RideData['turn'],
                        'notes' => $RideData['notes'],
                        'distance' => $RideData['distance'],
                        'completed' => 0,
                    ],
                );
            }
        }

        //$Cuesheet = $this->loadJson('Cuesheet.json');

        //dd($Cuesheet);


        // SHOULD THIS BE HERE???

        // foreach ($Cuesheet as $RideData) {

        // //dump($RideData);

        //     Ride::query()->updateOrCreate(
        //         [
        //             'ride' => $RideData['ride'],
        //             'turn' => $RideData['turn'],
        //             'notes' => $RideData['notes'],
        //             'distance' => $RideData['distance'],
        //         ],
        //     );
        // }
    }

    /** @return array<int, array<string, mixed>> */
    private function loadJson(string $filename): array
    {
        $path = database_path('data/'.$filename);

        return json_decode(file_get_contents($path), true);
    }
};
