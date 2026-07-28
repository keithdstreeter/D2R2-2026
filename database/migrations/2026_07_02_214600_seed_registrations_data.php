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
        $Registrations = $this->loadJson('registrations.json');

        if (! is_array($Registrations)) {
            logger()->error('seed_registrations_data: could not load registrations.json');

            return;
        }

        foreach ($Registrations as $RegistrationData) {

            Registration::query()->updateOrCreate(
                [
                    'bib' => $RegistrationData['bib'],
                    'first_name' => $RegistrationData['first_name'],
                    'last_name' => $RegistrationData['last_name'],
                    'phone' => $RegistrationData['phone'],
                    'category_entered' => $RegistrationData['category_entered'],
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

        return json_decode(file_get_contents($path), true);
    }
};
