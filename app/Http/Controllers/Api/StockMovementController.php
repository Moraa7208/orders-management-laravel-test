<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StockMovementResource;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\ManualStockAdjustmentRequest;


class StockMovementController extends Controller
{
    /**
     * @var StockMovementService
     */
    protected $stockMovementService;

    /**
     * StockMovementController constructor.
     *
     * @param StockMovementService $stockMovementService
     */
    public function __construct(StockMovementService $stockMovementService)
    {
        $this->stockMovementService = $stockMovementService;
    }

    /**
     * Display a listing of stock movements with filtering and pagination.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = [];
        
        // Extract filters from request
        if ($request->has('product_id')) {
            $filters['product_id'] = $request->product_id;
        }
        
        if ($request->has('warehouse_id')) {
            $filters['warehouse_id'] = $request->warehouse_id;
        }
        
        if ($request->has('date_from')) {
            $filters['date_from'] = $request->date_from;
        }
        
        if ($request->has('date_to')) {
            $filters['date_to'] = $request->date_to;
        }
        
        if ($request->has('reference_type')) {
            $filters['reference_type'] = $request->reference_type;
        }
        
        // Get per page value
        $perPage = $request->input('per_page', 15);
        
        // Get stock movements with pagination
        $stockMovements = $this->stockMovementService->getStockMovements($filters, $perPage);
        
        return StockMovementResource::collection($stockMovements);
    }

    /**
     * Create a manual stock adjustment.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function manualAdjustment(ManualStockAdjustmentRequest  $request)
    {
        // Create manual adjustment using validated data
        $movement = $this->stockMovementService->createManualAdjustment(
            $request->product_id,
            $request->warehouse_id,
            $request->quantity,
            $request->description
        );
        if ($movement->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $movement->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        try {
            // Create manual adjustment
            $movement = $this->stockMovementService->createManualAdjustment(
                $request->product_id,
                $request->warehouse_id,
                $request->quantity,
                $request->description
            );
            
            return new StockMovementResource($movement);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the stock adjustment',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}