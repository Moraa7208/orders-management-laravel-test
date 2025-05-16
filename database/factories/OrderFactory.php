<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Warehouse;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer' => $this->faker->name,
            'status' => $this->faker->randomElement(['active', 'completed', 'canceled']),
            'created_at' => now(),
            'completed_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'warehouse_id' => Warehouse::factory(),
         ];
    }
}
