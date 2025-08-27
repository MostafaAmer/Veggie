<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;


class CategoryService
{
    public function getAllCategoriesWithRelations()
    {
        return Category::with([
                'coverImage',
                'children.coverImage'
            ])
            ->withCount([
                'products',
                'products as active_products_count' => fn($q) => $q->active(),
                'children'
            ])
            ->whereNull('parent_id')
            ->ordered()
            ->get();
    }

    public function getTree()
    {
        return Category::with('coverImage')
            ->defaultOrder()
            ->get()
            ->toTree();
    }

    public function loadFullRelations(Category $category): Category
    {
        return $category->load([
            'parent',
            'children.coverImage',
            'products',
            'coverImage'
        ])->loadCount([
            'products',
            'products as active_products_count' => fn($q) => $q->active(),
            'children'
        ]);
    }

    public function createCategory(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $cat = Category::create($data);

            if (!empty($data['image'])) {
                $this->handleImage($cat, $data['image']);
            }

            return $cat;
        });
    }

    public function updateCategory(Category $category, array $data): void
    {
        DB::transaction(function () use ($category, $data) {
            $category->update($data);
            
            if (!empty($data['image'])) {
                $this->handleImage($category, $data['image']);
            }
        });
    }

    public function deleteCategory(Category $category): void
    {
        DB::transaction(function () use ($category) {
            $uncat = Category::firstOrCreate(
                ['slug' => 'uncategorized'],
                ['name' => 'غير مصنف', 'order' => 999, 'is_active' => true]
            );

            $category->products()->update(['category_id' => $uncat->id]);
            $category->children()->update(['parent_id' => $uncat->id]);

            $category->coverImage?->delete();
            $category->delete();
        });
    }

    public function getFeaturedCategories(int $limit = 5)
    {
        return Category::with('coverImage')
            ->where('is_featured', true)
            ->where('is_active', true)
            ->ordered()
            ->limit($limit)
            ->get();
    }

    public function rebuildTree(array $treeData): void
    {
        DB::transaction(fn() => $this->processTree($treeData));
    }

    protected function processTree(array $items, ?int $parentId = null): void
    {
        foreach ($items as $index => $item) {
            $cat = Category::find($item['id']);
            if ($cat) {
                $cat->update([
                    'parent_id' => $parentId,
                    'order'     => $index,
                ]);

                if (!empty($item['children'])) {
                    $this->processTree($item['children'], $cat->id);
                }
            }
        }
    }

    protected function handleImage(Category $category, $image): void
    {
        $category->coverImage()?->delete();
        
        $path    = $image->store('categories/'.date('Y/m'), 'public');
        $thumb   = $this->createThumbnail($image);
        
        $attachment = $category->coverImage()->create([
            'original_name'   => $image->getClientOriginalName(),
            'path'            => $path,
            'thumbnail_path'  => $thumb,
            'mime_type'       => $image->getMimeType(),
            'size'            => $image->getSize(),
            'type'            => 'category_image',
            'dimensions'      => getimagesize($image->getPathname()),
        ]);
        
        $category->update(['cover_image_id' => $attachment->id]);
    }

     protected function createThumbnail($image): string
    {
        $thumb = Image::make($image->getRealPath())
            ->resize(300, 300, fn($c) => $c->aspectRatio());
        
        $path = 'thumbnails/'.date('Y/m').'/'.$image->hashName();
        Storage::disk('public')->put($path, $thumb->encode());

        return $path;
    }
}