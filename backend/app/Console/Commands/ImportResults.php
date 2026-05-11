<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Repositories\OrderRepository;
use App\Repositories\PatientRepository;
use App\Repositories\TestResultRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Csv\Exception as CsvException;
use League\Csv\Reader;

class ImportResults extends Command
{
    private const array REQUIRED_HEADERS = [
        'patientId',
        'patientName',
        'patientSurname',
        'patientSex',
        'patientBirthDate',
        'orderId',
        'testName',
        'testValue',
        'testReference',
    ];
    protected $signature = 'app:import-results {file : Path to the CSV file}';

    protected $description = 'Import patients and test results from a CSV file';

    public function __construct(
        private readonly PatientRepository $patients,
        private readonly OrderRepository $orders,
        private readonly TestResultRepository $testResults,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = (string) $this->argument('file');

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        try {
            $csv = Reader::from($path);
            $csv->setDelimiter(';');
            $csv->setHeaderOffset(0);

            $missing = array_diff(self::REQUIRED_HEADERS, $csv->getHeader());

            if ($missing !== []) {
                $this->error('Missing CSV columns: ' . implode(', ', $missing));

                return self::FAILURE;
            }

            $records = $csv->getRecords();
        } catch (CsvException $e) {
            $this->error("Cannot read CSV: {$e->getMessage()}");

            return self::FAILURE;
        }

        $imported = 0;
        $failed = 0;

        foreach ($records as $line => $row) {
            try {
                $this->importRow($row);
                Log::channel('import')->info('Imported', ['line' => $line + 1, 'orderId' => $row['orderId'], 'patient' => $row['patientName'] . ' ' . $row['patientSurname']]);
                $imported++;
            } catch (\Throwable $e) {
                Log::channel('import')->error('Failed', ['line' => $line + 1, 'error' => $e->getMessage(), 'row' => $row]);
                $failed++;
            }
        }

        $this->info("Import complete. Imported: {$imported}, Failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** @param array<string, string> $row */
    private function importRow(array $row): void
    {
        foreach (['patientName', 'patientSurname', 'patientBirthDate', 'orderId', 'testName'] as $field) {
            if (trim($row[$field]) === '') {
                throw new \InvalidArgumentException("Field '{$field}' is empty.");
            }
        }

        DB::transaction(function() use ($row): void
        {
            $patient = $this->patients->firstOrCreate(
                $row['patientName'],
                $row['patientSurname'],
                [
                    'sex'        => $row['patientSex'],
                    'birth_date' => $row['patientBirthDate'],
                ],
            );

            $order = $this->orders->firstOrCreate($row['orderId'], $patient->id);

            $this->testResults->firstOrCreate(
                $order->id,
                $row['testName'],
                $row['testValue'],
                $row['testReference'],
            );
        });
    }
}
