<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'description'    => $this->description,
            'price'          => $this->price,
            'final_price'    => $this->final_price,
            $this->mergeWhen($this->is_on_discount, [
                'discount' => [
                    'original_price' => $this->price,
                    'price'          => $this->discount_price,
                    'percent'        => $this->discount_percentage,
                    'ends_at'        => $this->discount_end?->toIso8601String(),
                ],
            ]),
            'meta'           => [
                'active'        => $this->is_active,
                'approved'      => $this->is_approved,
                'featured'      => $this->is_featured,
                'rating'        => $this->average_rating,
                'reviews'       => $this->review_count,
                'can_order'     => $this->can_order,
                'out_of_stock'  => $this->is_out_of_stock,
            ],
            'category'    => new CategoryResource($this->whenLoaded('category')),
            'main_image'  => new AttachmentResource($this->whenLoaded('mainImage')),
            'images'      => AttachmentResource::collection($this->whenLoaded('productImages')),
            'tags'           => $this->whenLoaded('tags', fn() =>
                                  $this->tags->map(fn($tag) => [
                                      'id'   => $tag->id,
                                      'name' => $tag->name,
                                      'slug' => $tag->slug,
                                  ])
                              ),
            'dimensions'  => $this->whenNotNull($this->dimensions, [
                'width'  => $this->dimensions['width'],
                'height' => $this->dimensions['height'],
                'depth'  => $this->dimensions['depth'],
            ]),
            'created_at'     => $this->created_at->toIso8601String(),
            'updated_at'     => $this->updated_at->toIso8601String(),
        ];
    }
}