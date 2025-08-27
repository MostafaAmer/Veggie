<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'path'          => $this->full_path,
            'description'   => $this->description,
            'is_active'     => $this->is_active,
            'is_featured'     => $this->is_featured,
            'order'         => $this->order,
            'color'           => $this->color,
            'cover_image_url' => $this->cover_image_url,
            'meta' => [
                'title'             => $this->meta_title,
                'description'       => $this->meta_description,
                'keywords'          => $this->meta_keywords,
                'total_products'    => $this->whenCounted('products'),
                'active_products'   => $this->when($this->relationLoaded('products'), fn() => $this->products()->active()->count()),
                'children_count'    => $this->whenCounted('children'),
            ],
            'full_path'       => $this->full_path,
            'product_count'   => $this->product_count,
            'parent' => $this->whenLoaded('parent', fn() => [
                'id'   => $this->parent->id,
                'name' => $this->parent->name,
                'slug' => $this->parent->slug,
            ]),
            'children' => CategoryResource::collection(
                $this->whenLoaded('children')
            ),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}