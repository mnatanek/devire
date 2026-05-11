<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Patient;
use App\Models\TestResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use Tests\TestCase;

class ResultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_results_returns_patient_data(): void
    {
        $patient = Patient::factory()->create();
        $order = Order::factory()->create(['patient_id' => $patient->id]);
        TestResult::factory()->count(2)->create(['order_id' => $order->id]);

        $this->getJson('/api/results', ['Authorization' => 'Bearer ' . $this->tokenFor($patient)])
            ->assertOk()
            ->assertJsonStructure([
                'patient' => ['id', 'name', 'surname', 'sex', 'birthDate'],
                'orders'  => [['orderId', 'results' => [['name', 'value', 'reference']]]],
            ]);
    }

    public function test_results_returns_only_authenticated_patient_data(): void
    {
        $patient = Patient::factory()->create();
        $other = Patient::factory()->create();
        Order::factory()->create(['patient_id' => $other->id]);

        $response = $this->getJson('/api/results', ['Authorization' => 'Bearer ' . $this->tokenFor($patient)])
            ->assertOk();

        $this->assertCount(0, $response->json('orders'));
    }

    public function test_results_returns_401_with_invalid_token(): void
    {
        $this->getJson('/api/results', ['Authorization' => 'Bearer invalid.token.here'])
            ->assertStatus(401);
    }

    private function tokenFor(Patient $patient): string
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return (string) $guard->login($patient);
    }
}
