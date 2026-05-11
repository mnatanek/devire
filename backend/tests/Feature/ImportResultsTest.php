<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportResultsTest extends TestCase
{
    use RefreshDatabase;

    private const HEADERS = "patientId;patientName;patientSurname;patientSex;patientBirthDate;orderId;testName;testValue;testReference\n";

    /** @var string[] */
    private array $tempFiles = [];

    public function test_import_creates_patients_orders_and_results(): void
    {
        $path = $this->csvFile("1;Jan;Kowalski;m;1980-01-01;ORD-1;glucose;5.5;4-6\n");

        $this->artisan('app:import-results', ['file' => $path])->assertSuccessful();

        $this->assertDatabaseHas('patients', ['name' => 'Jan', 'surname' => 'Kowalski']);
        $this->assertDatabaseHas('orders', ['order_id' => 'ORD-1']);
        $this->assertDatabaseHas('test_results', ['name' => 'glucose', 'value' => '5.5']);
    }

    public function test_import_fails_when_file_not_found(): void
    {
        $this->artisan('app:import-results', ['file' => '/nonexistent/file.csv'])->assertFailed();
    }

    public function test_import_fails_when_columns_missing(): void
    {
        $path = $this->csvFile("name,surname\nJan,Kowalski\n", false);

        $this->artisan('app:import-results', ['file' => $path])->assertFailed();
    }

    public function test_import_skips_row_with_empty_required_field(): void
    {
        $path = $this->csvFile("1;;Kowalski;m;1980-01-01;ORD-1;glucose;5.5;4-6\n");

        $this->artisan('app:import-results', ['file' => $path])->assertFailed();

        $this->assertDatabaseCount('patients', 0);
    }

    public function test_import_does_not_duplicate_patient_on_reimport(): void
    {
        $path = $this->csvFile("1;Jan;Kowalski;m;1980-01-01;ORD-1;glucose;5.5;4-6\n");

        $this->artisan('app:import-results', ['file' => $path])->assertSuccessful();
        $this->artisan('app:import-results', ['file' => $path])->assertSuccessful();

        $this->assertDatabaseCount('patients', 1);
    }

    public function test_import_does_not_duplicate_test_result_on_reimport(): void
    {
        $path = $this->csvFile("1;Jan;Kowalski;m;1980-01-01;ORD-1;glucose;5.5;4-6\n");

        $this->artisan('app:import-results', ['file' => $path])->assertSuccessful();
        $this->artisan('app:import-results', ['file' => $path])->assertSuccessful();

        $this->assertDatabaseCount('test_results', 1);
    }

    public function test_import_fails_row_when_order_belongs_to_different_patient(): void
    {
        $existing = Patient::factory()->create(['name' => 'Anna', 'surname' => 'Nowak']);
        Order::factory()->create(['order_id' => 'ORD-1', 'patient_id' => $existing->id]);

        $path = $this->csvFile("2;Jan;Kowalski;m;1980-01-01;ORD-1;glucose;5.5;4-6\n");

        $this->artisan('app:import-results', ['file' => $path])->assertFailed();

        $this->assertDatabaseCount('test_results', 0);
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    private function csvFile(string $content, bool $withHeaders = true): string
    {
        $path = tempnam(sys_get_temp_dir(), 'import_test_');
        file_put_contents($path, $withHeaders ? self::HEADERS . $content : $content);
        $this->tempFiles[] = $path;

        return $path;
    }
}
