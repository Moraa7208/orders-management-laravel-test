<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create a new order with items.
     *
     * @param string $customer
     * @param int $warehouseId
     * @param array $items
     * @return Order
     * @throws InsufficientStockException
     */
    public function createOrder(string $customer, int $warehouseId, array $items): Order
    {
        // Check stock availability before proceeding
        $insufficientItems = $this->checkStockAvailability($items, $warehouseId);
        
        if (!empty($insufficientItems)) {
            throw new InsufficientStockException('Insufficient stock for some products', $insufficientItems);
        }
        
        return DB::transaction(function () use ($customer, $warehouseId, $items) {
            // Create the order
            $order = Order::create([
                'customer' => $customer,
                'warehouse_id' => $warehouseId,
                'status' => 'active',
                'created_at' => now(),
            ]);
            
            // Create order items and reduce stock
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'count' => $item['count'],
                ]);
                
                // Reduce stock
                $this->reduceStock($item['product_id'], $warehouseId, $item['count'], 'App\\Models\\Order', $order->id, 'Order creation');
            }
            
            return $order;
        });
    }
    
    /**
     * Update an existing order.
     *
     * @param Order $order
     * @param string|null $customer
     * @param array|null $items
     * @return Order
     * @throws InsufficientStockException
     */
    public function updateOrder(Order $order, ?string $customer, ?array $items): Order
    {
        return DB::transaction(function () use ($order, $customer, $items) {
            // Update customer information if provided
            if ($customer !== null) {
                $order->customer = $customer;
                $order->save();
            }
            
            // Update items if provided
            if ($items !== null) {
                // First, restore stock for all current items
                foreach ($order->orderItems as $orderItem) {
                    $this->increaseStock(
                        $orderItem->product_id, 
                        $order->warehouse_id, 
                        $orderItem->count,
                        'App\\Models\\Order',
                        $order->id,
                        'Order update - return stock'
                    );
                }
                
                // Check if new items have sufficient stock
                $insufficientItems = $this->checkStockAvailability($items, $order->warehouse_id);
                
                if (!empty($insufficientItems)) {
                    // Rollback by reducing stock again for all current items to restore original state
                    foreach ($order->orderItems as $orderItem) {
                        $this->reduceStock(
                            $orderItem->product_id, 
                            $order->warehouse_id, 
                            $orderItem->count,
                            'App\\Models\\Order',
                            $order->id,
                            'Order update rollback'
                        );
                    }
                    
                    throw new InsufficientStockException('Insufficient stock for some products', $insufficientItems);
                }
                
                // Delete existing items
                $order->orderItems()->delete();
                
                // Create new items and reduce stock
                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'count' => $item['count'],
                    ]);
                    
                    // Reduce stock for new items
                    $this->reduceStock(
                        $item['product_id'], 
                        $order->warehouse_id, 
                        $item['count'],
                        'App\\Models\\Order',
                        $order->id,
                        'Order update - allocate stock'
                    );
                }
            }
            
            $order->refresh();
            return $order;
        });
    }
    
    /**
     * Complete an order.
     *
     * @param Order $order
     * @return Order
     */
    public function completeOrder(Order $order): Order
    {
        $order->status = 'completed';
        $order->completed_at = now();
        $order->save();
        
        return $order;
    }

    /**
     * Cancel an order and return items to stock.
     *
     * @param Order $order
     * @return Order
     */
    public function cancelOrder(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            // Return all items to stock
            foreach ($order->orderItems as $orderItem) {
                $this->increaseStock(
                    $orderItem->product_id, 
                    $order->warehouse_id, 
                    $orderItem->count,
                    'App\\Models\\Order',
                    $order->id,
                    'Order cancellation'
                );
            }
            
            // Update order status
            $order->status = 'canceled';
            $order->save();
            
            return $order;
        });
    }

    /**
     * Resume a canceled order.
     *
     * @param Order $order
     * @return Order
     * @throws InsufficientStockException
     */
    public function resumeOrder(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            // Check if all items have sufficient stock
            $items = $order->orderItems->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'count' => $item->count,
                ];
            })->toArray();
            
            $insufficientItems = $this->checkStockAvailability($items, $order->warehouse_id);
            
            if (!empty($insufficientItems)) {
                throw new InsufficientStockException('Insufficient stock to resume the order', $insufficientItems);
            }
            
            // Reduce stock for all items
            foreach ($order->orderItems as $orderItem) {
                $this->reduceStock(
                    $orderItem->product_id, 
                    $order->warehouse_id, 
                    $orderItem->count,
                    'App\\Models\\Order',
                    $order->id,
                    'Order resumption'
                );
            }
            
            // Update order status
            $order->status = 'active';
            $order->save();
            
            return $order;
        });
    }

    /**
     * Check if there is sufficient stock for all items.
     *
     * @param array $items
     * @param int $warehouseId
     * @return array
     */
    protected function checkStockAvailability(array $items, int $warehouseId): array
    {
        $insufficientItems = [];

        foreach ($items as $item) {
            $stock = Stock::where('product_id', $item['product_id'])
                ->where('warehouse_id', $warehouseId)
                ->first();
                
            if (!$stock || $stock->stock < $item['count']) {
                $product = Product::find($item['product_id']);
                $availableStock = $stock ? $stock->stock : 0;
                
                $insufficientItems[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'requested_count' => $item['count'],
                    'available_count' => $availableStock,
                ];
            }
        }

        return $insufficientItems;
    }

    /**
     * Reduce stock for a product in a warehouse and record the movement.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param int $count
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @param string|null $description
     * @return void
     */
    protected function reduceStock(
        int $productId, 
        int $warehouseId, 
        int $count,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null
    ): void
    {
        // Get current stock
        $stock = Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();
            
        if (!$stock) {
            // If no stock record exists, create one with zero initial stock
            $stock = Stock::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'stock' => 0
            ]);
        }
        
        // Record movement before updating stock
        $balanceAfter = $stock->stock - $count;
        
        StockMovement::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'quantity' => -$count, // Negative for reduction
            'balance_after' => $balanceAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description
        ]);
        
        // Reduce stock
        $stock->decrement('stock', $count);
    }

    /**
     * Increase stock for a product in a warehouse and record the movement.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param int $count
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @param string|null $description
     * @return void
     */
    protected function increaseStock(
        int $productId, 
        int $warehouseId, 
        int $count,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null
    ): void
    {
        // Check if the stock record exists
        $stock = Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();
            
        if ($stock) {
            // Record movement before updating stock
            $balanceAfter = $stock->stock + $count;
            
            StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $count, // Positive for increase
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description
            ]);
            
            // Increase stock
            $stock->increment('stock', $count);
        } else {
            // Create new stock record if it doesn't exist
            $stock = Stock::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'stock' => $count,
            ]);
            
            // Record movement
            StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $count,
                'balance_after' => $count,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description
            ]);
        }
    }
}