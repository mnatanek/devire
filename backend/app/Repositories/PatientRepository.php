<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Patient;

class PatientRepository
{
    public function findByLogin(string $login): ?Patient
    {
        return Patient::all()->first(
            fn(Patient $patient) => $patient->name . $patient->surname === $login,
        );
    }

    /** @param array<string, string> $attributes */
    public function firstOrCreate(string $name, string $surname, array $attributes): Patient
    {
        return Patient::firstOrCreate(
            ['name' => $name, 'surname' => $surname],
            $attributes,
        );
    }
}
