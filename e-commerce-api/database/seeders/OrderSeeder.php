<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run()
    {
        Order::factory()
            ->count(20) // Create 20 orders
            ->has(
                OrderItem::factory()
                    ->count(3) // Each order has 3 items
                    ->state(function (array $attributes) {
                        return [
                            'product_id' => Product::factory(), // Create related products
                        ];
                    })
            )
            ->create();
    }
}
