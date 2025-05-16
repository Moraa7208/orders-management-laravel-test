<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;



class OrderController extends Controller
{

        /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * OrderController constructor.
     *
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
        /**
     * Display a listing of orders with filtering and pagination.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Order::query();
        
        // Apply filters if present
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('customer')) {
            $query->where('customer', 'like', '%' . $request->customer . '%');
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        
        // Eager load related data
        $query->with(['orderItems.product', 'warehouse']);
        
        // Implement pagination
        $perPage = $request->input('per_page', 15);
        $orders = $query->paginate($perPage);
        
        return OrderResource::collection($orders);
    }

       /**
     * Store a newly created order in storage.
     *
     * @param OrderCreateRequest $request
     * @return OrderResource|JsonResponse
     */
    public function store(OrderCreateRequest $request)
    {
        try {
            $order = $this->orderService->createOrder(
                $request->customer,
                $request->warehouse_id,
                $request->items
            );
            
            return new OrderResource($order->load(['orderItems.product', 'warehouse']));
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->getErrors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the order',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified order.
     *
     * @param OrderUpdateRequest $request
     * @param Order $order
     * @return OrderResource|JsonResponse
     */
    public function update(OrderUpdateRequest $request, Order $order)
    {
        try {
            // Only allow updating if order is in "active" status
            if ($order->status !== 'active') {
                return response()->json([
                    'message' => 'Only active orders can be updated'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            $updatedOrder = $this->orderService->updateOrder(
                $order,
                $request->customer,
                $request->items
            );
            
            return new OrderResource($updatedOrder->load(['orderItems.product', 'warehouse']));
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->getErrors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the order',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

       /**
     * Complete the specified order.
     *
     * @param Order $order
     * @return OrderResource|JsonResponse
     */
    public function complete(Order $order)
    {
        try {
            if ($order->status !== 'active') {
                return response()->json([
                    'message' => 'Only active orders can be completed'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            $completedOrder = $this->orderService->completeOrder($order);
            
            return new OrderResource($completedOrder);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while completing the order',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

      /**
     * Cancel the specified order.
     *
     * @param Order $order
     * @return OrderResource|JsonResponse
     */
    public function cancel(Order $order)
    {
        try {
            if ($order->status !== 'active') {
                return response()->json([
                    'message' => 'Only active orders can be canceled'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            $canceledOrder = $this->orderService->cancelOrder($order);
            
            return new OrderResource($canceledOrder);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while canceling the order',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Resume the specified canceled order.
     *
     * @param Order $order
     * @return OrderResource|JsonResponse
     */
    public function resume(Order $order)
    {
        try {
            if ($order->status !== 'canceled') {
                return response()->json([
                    'message' => 'Only canceled orders can be resumed'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            $resumedOrder = $this->orderService->resumeOrder($order);
            
            return new OrderResource($resumedOrder);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->getErrors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while resuming the order',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
