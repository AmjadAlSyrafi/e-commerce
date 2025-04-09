<?php

namespace App\Actions;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Lorisleiva\Actions\Concerns\AsAction;

class SendOrderDetailsAction
{
    use AsAction;


    public static function routes()
    {
        Route::get('/orders-details/{order}', self::class);
    }

    public function handle(int $orderId): array
    {
        $order = Order::with(['orderItems.product', 'user'])->findOrFail($orderId);

        return [
            'success' => true,
            'message' => 'Order details retrieved successfully',
            'data' => [
                'order_id' => $order->id,
                'user' => [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                ],
                'status' => $order->status,
                'total_price' => $order->total_price,
                'items' => $order->orderItems->map(function ($item) {
                    return [
                        'product_id' => $item->product->id,
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ];
                }),
                'created_at' => $order->created_at->toDateTimeString(),
            ],
        ];
    }

    /**
     * Define as a controller action.
     */
    public function asController(Order $order)
    {
        return response()->json($this->handle($order->id), 200);
    }
}
