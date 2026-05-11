<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Order;

class OrderRepository
{
    public function findByOrderId(string $orderId): ?Order
    {
        return Order::where('order_id', $orderId)->first();
    }

    public function firstOrCreate(string $orderId, int $patientId): Order
    {
        $order = $this->findByOrderId($orderId);

        if ($order !== null) {
            if ($order->patient_id !== $patientId) {
                throw new \RuntimeException("Order {$orderId} belongs to a different patient.");
            }

            return $order;
        }

        return Order::create(['order_id' => $orderId, 'patient_id' => $patientId]);
    }
}
