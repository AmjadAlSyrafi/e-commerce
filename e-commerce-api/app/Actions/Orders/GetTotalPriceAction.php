<?php


namespace App\Actions\Orders;

use App\Models\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTotalPriceAction
{
    use AsAction;

    public function handle(int $orderId): float
    {
        $order = Order::with('orderItems')->findOrFail($orderId);
        
        return $order->orderItems->sum(function ($item) {
             $total =  $item->price;
            return round($total, 2);

        });
    }
}


