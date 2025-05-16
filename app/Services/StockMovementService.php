<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockMovement;

class StockMovementService
{
    /**
     * Get stock movements with filtering and pagination.
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getStockMovements(array $filters = [], int $perPage = 15)
    {
        $query = StockMovement::query();
        
        // Apply filters
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        
        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }
        
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        if (isset($filters['reference_type'])) {
            $query->where('reference_type', $filters['reference_type']);
        }
        
        // Eager load related data
        $query->with(['product', 'warehouse']);
        
        // Order by creation date (newest first)
        $query->orderBy('created_at', 'desc');
        
        return $query->paginate($perPage);
    }

    /**
     * Create a manual stock adjustment and record the movement.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param int $quantity
     * @param string $description
     * @return StockMovement
     */
    public function createManualAdjustment(int $productId, int $warehouseId, int $quantity, string $description): StockMovement
    {
        return \DB::transaction(function () use ($productId, $warehouseId, $quantity, $description) {
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
            
            // Calculate new balance
            $balanceAfter = $stock->stock + $quantity;
            
            // Create movement record
            $movement = StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'balance_after' => $balanceAfter,
                'reference_type' => 'manual',
                'reference_id' => null,
                'description' => $description
            ]);
            
            // Update stock
            if ($quantity > 0) {
                $stock->increment('stock', $quantity);
            } else {
                $stock->decrement('stock', abs($quantity));
            }
            
            return $movement;
        });
    }
}