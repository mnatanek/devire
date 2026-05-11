<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\TestResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TestResult> */
class TestResultFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id'  => Order::factory(),
            'name'      => $this->faker->word(),
            'value'     => (string) $this->faker->randomFloat(2, 0, 100),
            'reference' => $this->faker->numerify('#-##'),
        ];
    }
}
