<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $categories = ['Electronics', 'Clothing', 'Food', 'Books', 'Furniture', 'Sports', 'Toys', 'Beauty', 'Health', 'Home'];
        $category = $this->faker->randomElement($categories);
        
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'sku' => strtoupper(substr($category, 0, 3)) . $this->faker->unique()->numberBetween(1000, 9999),
            'category' => $category,
            'price' => $this->faker->randomFloat(2, 5, 1000),
        ];
    }
}