<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Warehouse::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $city = $this->faker->city;
        
        return [
            'name' => "{$city} Warehouse",
            'location' => "{$city}, " . $this->faker->country,
            'code' => strtoupper(substr($city, 0, 3)) . $this->faker->unique()->numberBetween(10, 99),
            'status' => 'active',
        ];
    }
}