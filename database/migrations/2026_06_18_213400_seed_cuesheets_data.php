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

        $this->seedCuesheet();
    }

    public function down(): void
    {
        Ride::query()->delete();
    }

    private function seedCuesheet(): void
    {
        $Cuesheet = $this->loadJson('Cuesheet.json');

        //dd($Cuesheet);

        foreach ($Cuesheet as $RideData) {

        //dump($RideData);
       

            Ride::query()->updateOrCreate(
                [
                    'ride' => $RideData['Ride'],
                    'turn' => $RideData['Turn'],
                    'notes' => $RideData['Notes'],
                    'distance' => $RideData['Distance'],
                ],
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function loadJson(string $filename): array
    {
        $path = database_path('data/'.$filename);

        return json_decode(file_get_contents($path), true);
    }
};
