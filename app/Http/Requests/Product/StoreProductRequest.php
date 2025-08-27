<?php
declare(strict_types=1);
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', Product::class);
    }

    public function rules()
    {
        return [
            'name'                => 'required|string|max:255',
            'description'         => 'required|string|min:20',
            'price'               => 'required|numeric|min:0.01|max:999999',
            'cost_price'          => 'nullable|numeric|min:0|max:999999',
            'stock'               => 'required|integer|min:0|max:999999',
            'category_id'         => 'required|exists:categories,id',
            'sku'                 => 'nullable|string|unique:products,sku|max:50',
            'barcode'             => 'nullable|string|unique:products,barcode|max:50',
            'images'              => 'required|array|min:1|max:10',
            'images.*'            => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'tags'                => 'sometimes|array|max:10',
            'tags.*'              => 'string|max:50',
            'is_active'           => 'sometimes|boolean',
            'is_featured'         => 'sometimes|boolean',
            'weight'              => 'nullable|numeric|min:0',
            'dimensions'          => 'nullable|string|max:100',
            'min_order_quantity'  => 'sometimes|integer|min:1',
            'max_order_quantity'  => 'sometimes|integer|gt:min_order_quantity',
            'meta_title'          => 'nullable|string|max:60',
            'meta_description'    => 'nullable|string|max:160',
            'meta_keywords'       => 'nullable|array',
            'meta_keywords.*'     => 'string|max:50',
            'unit'                => 'sometimes|string|in:piece,kg,gram,liter,pack',
            'is_virtual'          => 'sometimes|boolean',
            'download_link'       => 'required_if:is_virtual,true|url'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'اسم المنتج مطلوب',
            'description.min'         => 'يجب أن يكون الوصف على الأقل 20 حرفًا',
            'price.min'               => 'يجب أن يكون السعر أكبر من الصفر',
            'images.required'         => 'يجب رفع صورة واحدة على الأقل',
            'images.max'              => 'لا يمكن رفع أكثر من 10 صور',
            'tags.max'                => 'لا يمكن إضافة أكثر من 10 وسمات',
            'max_order_quantity.gt'   => 'يجب أن تكون الكمية القصوى أكبر من الكمية الدنيا'
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'التصنيف',
            'cost_price' => 'سعر التكلفة'
        ];
    }
}