<?php

namespace App\Actions\Orders;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateOrderStatusAction
{
    use AsAction;

    /**
     * Handles order status updates.
     *
     * @param int $orderId The ID of the order being updated.
     * @param string $newStatus The new status for the order.
     * @return JsonResponse
     */
    public function handle(int $orderId, string $newStatus): JsonResponse
    {
        $validStatuses = ['pending', 'completed', 'canceled'];

        if (!in_array($newStatus, $validStatuses)) {
            return response()->json(['success' => false, 'message' => "Invalid status: {$newStatus}."], 400);
        }

        $order = Order::with('orderItems.product')->find($orderId);

        if (!$order) {
            return response()->json(['success' => false, 'message' => "Order not found."], 404);
        }

        $previousStatus = $order->status;

        //  If changing to 'completed', ensure stock availability and decrement it
        if ($previousStatus !== 'completed' && $newStatus === 'completed') {
            foreach ($order->orderItems as $item) {
                $product = $item->product;

                if ($product->stock < $item->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for product ID {$product->id}. Available: {$product->stock}, Required: {$item->quantity}"
                    ], 400);
                }

                //  Reduce stock
                $product->decrement('stock', $item->quantity);
            }
        }

        //  If changing to 'canceled', restore the stock
        if ($previousStatus === 'completed' && $newStatus === 'canceled') {
            foreach ($order->orderItems as $item) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        //  Update the order status
        $order->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => "Order status updated successfully.",
            'data' => $order,
        ], 200);
    }
}
