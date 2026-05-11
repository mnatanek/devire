<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id'   => $this->faker->unique()->numerify('ORD-####'),
            'patient_id' => Patient::factory(),
        ];
    }
}
