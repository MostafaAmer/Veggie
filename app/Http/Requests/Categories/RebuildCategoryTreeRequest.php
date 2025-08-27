<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Category;

class RebuildCategoryTreeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', Category::class);
    }

    public function rules(): array
    {
        return [
            'tree'                  => 'required|array|min:1',
            'tree.*.id'             => 'required|exists:categories,id',
            'tree.*.children'       => 'sometimes|array',
            'tree.*.children.*.id'  => 'required_with:tree.*.children|exists:categories,id',
        ];
    }
}
