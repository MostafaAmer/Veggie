<?php
// app/Http/Requests/StoreCategoryRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Category;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Category::class);
    }

    public function rules()
    {
        return [
            'name'                 => 'required|string|max:255',
            'slug'                 => 'sometimes|string|max:255|unique:categories,slug',
            'description'          => 'nullable|string',
            'status'               => 'sometimes|in:active,inactive,archived',
            'is_active'            => 'sometimes|boolean',
            'is_featured'          => 'sometimes|boolean',
            'parent_id'            => 'nullable|exists:categories,id',
            'order'                => 'sometimes|integer|min:0',
            'color'                => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'meta_title'           => 'nullable|string|max:60',
            'meta_description'     => 'nullable|string|max:160',
            'meta_keywords'        => 'nullable|array',
            'meta_keywords.*'      => 'string|max:50',
            'image'                => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }
}