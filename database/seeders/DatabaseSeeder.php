<?php

namespace Database\Seeders;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Warehouse;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
    $warehouses = Warehouse::factory()->count(5)->create();

    // Create 10 products
    $products = Product::factory()->count(10)->create();

    // Create 20 orders with items
    Order::factory()
        ->count(20)
        ->make()
        ->each(function ($order) use ($warehouses, $products) {
            $order->warehouse_id = $warehouses->random()->id;
            $order->save();

            // Create 2-4 order items for each order
            OrderItem::factory()
                ->count(rand(2, 4))
                ->make()
                ->each(function ($item) use ($order, $products) {
                    $item->order_id = $order->id;
                    $item->product_id = $products->random()->id;
                    $item->count = rand(1, 5);
                    $item->save();
                });
        });
    }
}
