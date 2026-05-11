<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_with_valid_credentials(): void
    {
        $patient = Patient::factory()->create(['birth_date' => '1983-04-12']);

        $response = $this->postJson('/api/login', [
            'login'    => $patient->name . $patient->surname,
            'password' => '1983-04-12',
        ]);

        $response->assertOk()->assertJsonStructure(['token']);
    }

    public function test_login_returns_401_with_wrong_birth_date(): void
    {
        $patient = Patient::factory()->create(['birth_date' => '1983-04-12']);

        $this->postJson('/api/login', [
            'login'    => $patient->name . $patient->surname,
            'password' => '2000-01-01',
        ])->assertStatus(401);
    }

    public function test_login_returns_401_for_unknown_patient(): void
    {
        $this->postJson('/api/login', [
            'login'    => 'NotExistingPatient',
            'password' => '1983-04-12',
        ])->assertStatus(401);
    }

    public function test_login_returns_422_when_fields_missing(): void
    {
        $this->postJson('/api/login', [])->assertStatus(422);
    }

    public function test_results_returns_401_without_token(): void
    {
        $this->getJson('/api/results')->assertStatus(401);
    }
}
