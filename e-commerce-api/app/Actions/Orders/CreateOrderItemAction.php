<?php

namespace App\Actions\Orders;

use App\Models\OrderItem;
use App\Models\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrderItemAction
{
    use AsAction;

    /**
     * Handles the creation of an order item.
     *
     * @param int $orderId
     * @param int $productId
     * @param int $quantity
     * @return array|OrderItem
     */
    public function handle(int $orderId, int $productId, int $quantity): array|OrderItem
    {
        $product = Product::find($productId);

        if (!$product) {
            return ['error' => "Product with ID {$productId} not found."];
        }

        if ($product->stock < $quantity) {
            return ['error' => "Insufficient stock for product ID {$productId}. Available: {$product->stock}"];
        }

        return OrderItem::create([
            'order_id' => $orderId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price' => $product->price * $quantity,
        ]);
    }
}
