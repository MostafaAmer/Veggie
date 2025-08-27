<?php
declare(strict_types=1);
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Product;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    public function rules(): array
    {
        $productId = $this->route('product')->getKey();
        return [
            'name'                => 'sometimes|string|max:255',
            'description'         => 'sometimes|string|min:20',
            'price'               => 'sometimes|numeric|min:0.01|max:999999',
            'cost_price'          => 'nullable|numeric|min:0|max:999999',
            'stock'               => 'sometimes|integer|min:0|max:999999',
            'category_id'         => 'sometimes|exists:categories,id',
            'sku'                 => ['sometimes','string','max:50', Rule::unique('products','sku')->ignore($productId)],
            'barcode'             => ['sometimes','string','max:50', Rule::unique('products','barcode')->ignore($productId)],
            'images'              => 'sometimes|array|max:10',
            'images.*'            => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'tags'                => 'sometimes|array|max:10',
            'tags.*'              => 'string|max:50',
            'is_active'           => 'sometimes|boolean',
            'is_featured'         => 'sometimes|boolean',
            'weight'              => 'nullable|numeric|min:0',
            'dimensions'          => 'nullable|string|max:100',
            'min_order_quantity'  => 'sometimes|integer|min:1',
            'max_order_quantity'  => 'sometimes|integer|gt:min_order_quantity',
            'discount_price'      => 'nullable|numeric|lt:price',
            'discount_start'      => 'nullable|date|required_with:discount_price',
            'discount_end'        => 'nullable|date|after:discount_start|required_with:discount_price'
        ];
    }

    public function messages(): array
    {
        return [
            'discount_price.lt'    => 'يجب أن يكون سعر الخصم أقل من السعر الأصلي',
            'discount_end.after'   => 'يجب أن يكون تاريخ انتهاء الخصم بعد تاريخ البداية'
        ];
    }
}