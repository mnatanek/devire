<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Patient;
use App\Models\TestResult;
use Illuminate\Http\JsonResponse;

class ResultsController extends Controller
{
    public function index(): JsonResponse
    {
        /** @var Patient|null $patient */
        $patient = auth('api')->user();

        if ($patient === null) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $patient->load('orders.testResults');

        return response()->json([
            'patient' => [
                'id'        => $patient->id,
                'name'      => $patient->name,
                'surname'   => $patient->surname,
                'sex'       => $patient->sex,
                'birthDate' => $patient->birth_date->toDateString(),
            ],
            'orders'  => $patient->orders->map(fn(Order $order): array => [
                'orderId' => $order->order_id,
                'results' => $order->testResults->map(fn(TestResult $result): array => [
                    'name'      => $result->name,
                    'value'     => $result->value,
                    'reference' => $result->reference,
                ])->all(),
            ])->all(),
        ]);
    }
}
