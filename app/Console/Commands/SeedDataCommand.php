<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Models\StockMovement;
use Faker\Factory as Faker;

class SeedDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-test-data {--products=20 : Number of products to create} {--warehouses=5 : Number of warehouses to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with test data for products, warehouses, and stock';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $faker = Faker::create();

        $productCount = (int)$this->option('products');
        $warehouseCount = (int)$this->option('warehouses');

        $this->info('Starting to seed test data...');
        $this->info("Products to create: {$productCount}");
        $this->info("Warehouses to create: {$warehouseCount}");

        // Create products
        $this->info("Creating {$productCount} products...");
        $progressBar = $this->output->createProgressBar($productCount);
        $products = [];

        for ($i = 0; $i < $productCount; $i++) {
            $products[] = Product::create([
                'name' => $faker->unique()->words(rand(1, 3), true) . ' ' . $faker->randomElement(['Basic', 'Premium', 'Ultra', 'Pro', 'Elite']),
                'price' => $faker->randomFloat(2, 5, 1000),
            ]);
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->newLine();

        // Create warehouses
        $this->info("Creating {$warehouseCount} warehouses...");
        $progressBar = $this->output->createProgressBar($warehouseCount);
        $warehouses = [];

        for ($i = 0; $i < $warehouseCount; $i++) {
            $warehouses[] = Warehouse::create([
                'name' => $faker->unique()->city() . ' Warehouse',
            ]);
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->newLine();

        // Create stock entries
        $this->info("Creating stock entries...");
        $progressBar = $this->output->createProgressBar(count($products) * count($warehouses));

        foreach ($products as $product) {
            foreach ($warehouses as $warehouse) {
                // Generate random stock amount (some products might have zero stock)
                $stockAmount = $faker->randomElement([0, 0, $faker->numberBetween(1, 100), $faker->numberBetween(10, 500)]);
                
                Stock::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'stock' => $stockAmount,
                ]);

                // If stock is added, create a stock movement record
                if ($stockAmount > 0) {
                    StockMovement::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                        'quantity' => $stockAmount,
                        'balance_after' => $stockAmount,
                        'reference_type' => 'initial_load',
                        'reference_id' => null,
                        'description' => 'Initial inventory load',
                    ]);
                }
                
                $progressBar->advance();
            }
        }
        $progressBar->finish();
        $this->newLine();

        $this->info('Test data seeding completed successfully!');
        $this->info('Summary:');
        $this->info("- {$productCount} products created");
        $this->info("- {$warehouseCount} warehouses created");
        $this->info("- " . ($productCount * $warehouseCount) . " stock entries created");
    }
}