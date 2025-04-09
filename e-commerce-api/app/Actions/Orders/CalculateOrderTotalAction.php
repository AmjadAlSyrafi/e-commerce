<?php

namespace App\Actions\Orders;

use App\Models\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class CalculateOrderTotalAction
{
    use AsAction;

    public function handle(int $orderId): float
    {
        $order = Order::with('orderItems')->findOrFail($orderId);

        $totalPrice = $order->orderItems->sum(fn($item) => $item->quantity * $item->price);

        // Ensure we update the correct total in the database
        $order->update(['total_price' => $totalPrice]);

        return round($totalPrice, 2);
    }
}
