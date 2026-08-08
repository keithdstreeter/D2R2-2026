<?php

use App\Models\Registration;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $this->seedRegistrations();
    }

    public function down(): void
    {
        Registration::query()->delete();
    }

    private function seedRegistrations(): void
    {
        $Registrations = $this->loadJson('Registrations.json');

        if (! is_array($Registrations)) {
            logger()->error('seed_registrations_data: could not load Registrations.json');

            return;
        }

        foreach ($Registrations as $RegistrationData) {

            if ($RegistrationData['category_entered'] == '100K (The Original!)') {
                $rideShortName = '100K';
            } elseif ($RegistrationData['category_entered'] == '140K (NEW for 2026)') {
                $rideShortName = '140K';
            } elseif ($RegistrationData['category_entered'] == 'Family Ride') {
                $rideShortName = 'Family-GR';
            } elseif ($RegistrationData['category_entered'] == 'Family Ride (under 12 years old)') {
                $rideShortName = 'Family-CB';
            } elseif ($RegistrationData['category_entered'] == 'Green River Tour') {
                $rideShortName = 'GRR+12.7';
            } elseif ($RegistrationData['category_entered'] == 'Green River Tour (under 18 years old)') {
                $rideShortName = 'GRR';
            } elseif ($RegistrationData['category_entered'] == 'Point to Point Ride, 52mi (NEW for 2026)') {
                $rideShortName = '80k';
            } else {
                $rideShortName = $RegistrationData['category_entered'];
            }    

            Registration::query()->updateOrCreate(
                [
                    'bib' => $RegistrationData['bib'],
                    'first_name' => $RegistrationData['first_name'],
                    'last_name' => $RegistrationData['last_name'],
                    'phone' => $RegistrationData['phone'],
                    'category_entered' => $rideShortName,
                    'email' => $RegistrationData['email'],
                    'dob' => $RegistrationData['dob'],
                    'gender' => $RegistrationData['gender'],
                ],
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function loadJson(string $filename): array
    {
        $path = database_path('data/'.$filename);

        if (! is_file($path)) {
            logger()->error('seed_registrations_data: seed file missing', [
                'path' => $path,
            ]);

            return [];
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            logger()->error('seed_registrations_data: unable to read seed file', [
                'path' => $path,
            ]);

            return [];
        }

        $decoded = json_decode($contents, true);

        return is_array($decoded) ? $decoded : [];
    }
};
