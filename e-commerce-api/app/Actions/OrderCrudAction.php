<?php

namespace App\Actions;

use App\Models\Order;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Http\JsonResponse;
use App\Actions\Orders\CreateOrderAction;
use App\Actions\Orders\CreateOrderItemAction;
use App\Actions\Orders\CalculateOrderTotalAction;
use App\Actions\Orders\UpdateOrderStatusAction;

class OrderCrudAction
{
    use AsAction;

    /**
     * Handles CRUD operations dynamically.
     */
    public function handle(Request|array $data, ?int $orderId = null, string $action = 'index'): JsonResponse
    {
        $isApiRequest = $data instanceof Request;
        $validatedData = $isApiRequest ? $data->all() : $data;

        return match ($action) {
            'index' => $this->index(),
            'store' => $this->store($validatedData),
            'show' => $this->show($orderId),
            'update' => $this->update($validatedData, $orderId),
            'destroy' => $this->destroy($orderId),
            default => response()->json(['success' => false, 'message' => "Invalid action: $action"], 400),
        };
    }

    /**
     * Retrieve all orders.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully.',
            'data' => Order::with('orderItems.product')->get(),
        ], 200);
    }

    /**
     * Store a new order without decrementing stock.
     */
    public function store(array $data): JsonResponse
    {
        if (!isset($data['user_id']) || empty($data['items'])) {
            return response()->json([
                'success' => false,
                'message' => "Missing required fields: 'user_id' and 'items'."
            ], 400);
        }

        //  Create the Order
        $order = CreateOrderAction::run([
            'user_id' => $data['user_id'],
        ]);

        $totalPrice = 0;

        foreach ($data['items'] as $item) {
            if (!isset($item['product_id'], $item['quantity'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Each item must include 'product_id' and 'quantity'."
                ], 400);
            }

            $createdItem = CreateOrderItemAction::run($order->id, $item['product_id'], $item['quantity']);

            $totalPrice += $createdItem->price;

        }
        round($totalPrice, 2);
        //  Ensure total price is updated
        $order->update(['total_price' => $totalPrice]);

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully.',
            'data' => $order->load('orderItems.product'),
        ], 201);
    }

    /**
     * Update an order status and decrement stock if completed.
     */
    public function update(array $data, ?int $orderId): JsonResponse
    {
        if (!$orderId) {
            return response()->json(['success' => false, 'message' => "Order ID is required."], 400);
        }

        if (!isset($data['status'])) {
            return response()->json(['success' => false, 'message' => "Status is required."], 400);
        }

        return UpdateOrderStatusAction::run($orderId, $data['status']);
    }

    /**
     * Retrieve a specific order.
     */
    public function show(?int $orderId): JsonResponse
    {
        if (!$orderId) {
            return response()->json(['success' => false, 'message' => "Order ID is required."], 400);
        }

        $order = Order::with('orderItems.product')->find($orderId);

        if (!$order) {
            return response()->json(['success' => false, 'message' => "Order not found."], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved successfully.',
            'data' => $order,
        ], 200);
    }

    /**
     * Delete an order.
     */
    public function destroy(?int $orderId): JsonResponse
    {
        if (!$orderId) {
            return response()->json(['success' => false, 'message' => "Order ID is required."], 400);
        }

        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['success' => false, 'message' => "Order not found."], 404);
        }

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully.',
        ], 200);
    }
}
