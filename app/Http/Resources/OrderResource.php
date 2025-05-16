<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'customer' => $this->customer,
            'status' => $this->status,
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => $this->when($this->relationLoaded('warehouse'), function () {
                return [
                    'id' => $this->warehouse->id,
                    'name' => $this->warehouse->name,
                ];
            }),
            'items' => $this->when($this->relationLoaded('orderItems'), function () {
                return OrderItemResource::collection($this->orderItems);
            }),
            'items_count' => $this->when($this->relationLoaded('orderItems'), function () {
                return $this->orderItems->count();
            }),
            'total' => $this->when(
     $this->relationLoaded('orderItems') &&
                $this->orderItems->isNotEmpty() &&
                $this->orderItems->first()->relationLoaded('product'), function () {
                return $this->orderItems->sum(function ($item) {
                    return $item->product->price * $item->count;
                });
            }),
            'created_at' => $this->created_at,
            'completed_at' => $this->completed_at,
            'updated_at' => $this->updated_at,
        ];
    }
}