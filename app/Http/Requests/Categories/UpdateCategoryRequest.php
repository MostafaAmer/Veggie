<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    protected $protectedSlugs = ['uncategorized', 'featured', 'default'];
    
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('category'));
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $rules = [
            'name'                 => 'sometimes|string|max:255',
            'slug'                 => [
                                        'sometimes',
                                        'string',
                                        'max:255',
                                        Rule::unique('categories','slug')->ignore($category->id),
                                        Rule::notIn($this->protectedSlugs)
                                      ],
            'description'          => 'nullable|string',
            'status'               => 'sometimes|in:active,inactive,archived',
            'is_active'            => 'sometimes|boolean',
            'is_featured'          => 'sometimes|boolean',
            'parent_id'            => [
                                        'nullable',
                                        'exists:categories,id',
                                        function ($attr, $value, $fail) use ($category) {
                                            if ($value == $category->id) {
                                                return $fail('لا يمكن تعيين التصنيف كوالد لنفسه');
                                            }
                                            if ($category->descendants()->pluck('id')->contains($value)) {
                                                return $fail('لا يمكن تعيين تصنيف فرعي كتصنيف أب');
                                            }
                                        }
                                      ],
            'order'                => 'sometimes|integer|min:0',
            'color'                => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'meta_title'           => 'nullable|string|max:60',
            'meta_description'     => 'nullable|string|max:160',
            'meta_keywords'        => 'nullable|array',
            'meta_keywords.*'      => 'string|max:50',
            'image'                => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

        ];
        return $rules;
    }

    public function messages()
    {
        return [
            'slug.not_in'       => 'هذا الرابط محجوز ولا يمكن استخدامه',
            'parent_id.exists'  => 'التصنيف الأب المحدد غير موجود',
            'image.max'         => 'يجب أن لا يتجاوز حجم الصورة 2 ميجابايت',
            'image.mimes'       => 'يجب أن تكون الصورة من نوع: jpeg, png, jpg, gif, webp'
        ];
    }

    public function attributes()
    {
        return [
            'name'      => 'اسم التصنيف',
            'slug'      => 'رابط التصنيف',
            'parent_id' => 'التصنيف الأب'
        ];
    }
}