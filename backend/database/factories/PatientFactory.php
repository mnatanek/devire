<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Patient> */
class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'       => $this->faker->firstName(),
            'surname'    => $this->faker->lastName(),
            'sex'        => $this->faker->randomElement(['male', 'female']),
            'birth_date' => $this->faker->date(),
        ];
    }
}
