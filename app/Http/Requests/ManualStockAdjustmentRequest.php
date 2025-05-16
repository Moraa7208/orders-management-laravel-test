<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManualStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'quantity' => 'required|integer|not_in:0',
            'description' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.not_in' => 'Quantity cannot be zero.',
        ];
    }
}
