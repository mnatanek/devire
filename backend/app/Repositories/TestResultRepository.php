<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\TestResult;

class TestResultRepository
{
    public function firstOrCreate(int $orderId, string $name, string $value, string $reference): TestResult
    {
        return TestResult::firstOrCreate(
            ['order_id' => $orderId, 'name' => $name],
            ['value' => $value, 'reference' => $reference],
        );
    }
}
