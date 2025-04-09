<?php

namespace App\Http\Controllers;

use App\Actions\OrderCrudAction;
use App\Http\Requests\OrderRequest;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * List all orders.
     */
    public function index()
    {
        return OrderCrudAction::run([], null, 'index');
    }

    /**
     * Retrieve a specific order.
     */
    public function show(Order $order)
    {
        return OrderCrudAction::run([], $order->id, 'show');
    }

    /**
     * Store a new order.
     */
    public function store(OrderRequest $request)
    {
        return OrderCrudAction::run($request->validated(), null, 'store');
    }

    /**
     * Update an order status.
     */
    public function update(Request $request, Order $order)
    {
        return OrderCrudAction::run($request->validated(), $order->id, 'update');
    }

    /**
     * Delete an order.
     */
    public function destroy(Order $order)
    {
        return OrderCrudAction::run([], $order->id, 'destroy');
    }
}
