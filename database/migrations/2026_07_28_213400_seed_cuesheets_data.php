<?php

use App\Models\Ride;
use App\Models\Cuesheet;
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
        //Ride::query()->delete();
        Cuesheet::query()->delete();
    }

    private function seedCuesheet(): void
    {

        // New - load all rides from table and then load each ride json file and insert into cuesheet table
        $query = Ride::all();
        
        //dd($query);

        foreach ($query as $ride) {
            try {
                $filename = 'cuesheet_'.$ride->ride.'.json';
                $Cuesheet = $this->loadJson($filename);
                //dump($filename);
                foreach ($Cuesheet as $RideData) {

                    // Handle ride names from the JSON file that may not match the ride name in the database
                    
                   if ($RideData['ride'] === '100K (The Original!)') {
                       $rideShortName = '100K';
                   } elseif ($RideData['ride'] === '140K (NEW for 2026)') {
                       $rideShortName = '140K';
                   } elseif ($RideData['ride'] === 'Family Ride') {
                       $rideShortName = 'Family-GR';
                   } elseif ($RideData['ride'] === 'Family Ride (under 12 years old)') {
                       $rideShortName = 'Family-CB';
                   } elseif ($RideData['ride'] === 'Green River Tour') {
                       $rideShortName = 'GRR+12.7';
                   } elseif ($RideData['ride'] === 'Green River Tour (under 18 years old)') {
                       $rideShortName = 'GRR';
                   } elseif ($RideData['ride'] === 'Point to Point Ride, 52mi (NEW for 2026)') {
                       $rideShortName = '80k';
                   } else {
                       $rideShortName = $RideData['ride'];
                   }    
                    
                    
                    // $rideShortName = $RideData['ride'];  // This line is no longer needed


                    Cuesheet::query()->updateOrCreate(
                        [
                            'ride' => $rideShortName,
                            'turn' => $RideData['turn'],
                            'notes' => $RideData['notes'],
                            'distance' => $RideData['distance'],
                            'completed' => 0,
                        ],
                    );
                }
            } catch (\Throwable $e) {
                Log::error('Failed to load cuesheet JSON for ride '.$ride->ride.': '.$e->getMessage());
                continue; // Skip to the next ride if there's an error
            }
        }

    }

    /** @return array<int, array<string, mixed>> */
    private function loadJson(string $filename): array
    {
        $path = database_path('data/'.$filename);

        return json_decode(file_get_contents($path), true);
    }
};
