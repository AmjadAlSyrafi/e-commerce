<?php

namespace App\Actions\Orders;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrderAction
{
    use AsAction;

    /**
     * Creates a new order.
     *
     * @param array $orderData ['user_id' => int]
     * @return Order
     */
    public function handle(array $orderData): Order
    {
        return DB::transaction(function () use ($orderData) {
            if (!isset($orderData['user_id'])) {
                ['error' => "Invalid input: 'user_id' is required."];
            }

            return Order::create([
                'user_id' => $orderData['user_id'],
                'status' => 'pending',
                'total_price' => 0,
            ]);
        });
    }
}
