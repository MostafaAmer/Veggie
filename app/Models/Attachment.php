<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{MorphTo, BelongsTo, HasMany, BelongsToMany};
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Models\Traits\{HasFileStorage, HasFileProperties, HasImageVariants, HasAttachmentScopes};
use App\Enums\AttachmentType;

class Attachment extends Model
{
    use SoftDeletes, HasFileStorage, HasFileProperties, HasImageVariants, HasAttachmentScopes;

    public $incrementing = false;
    protected $keyType = 'string';


    protected $fillable = [
        'original_name',
        'path',
        'mime_type',
        'file_size',
        'uploaded_by',
        'disk',
        'type',
        'custom_properties',
        'hash',
        'width',
        'height',
        'duration',
        'alt_text',
    ];

    protected $casts = [
        'custom_properties' => 'array',
        'file_size'         => 'integer',
        'width'             => 'integer',
        'height'            => 'integer',
        'duration'          => 'integer',
        'type'              => AttachmentType::class,
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

     public function getUrlAttribute(): ?string
    {
        if (!$this->path) {
            return null;
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    public function getFullPathAttribute(): ?string
    {
        if (!$this->path) {
            return null;
        }

        return Storage::disk($this->disk)->path($this->path);
    }

    public function getFileSizeAttribute(): string
    {
        $size = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->is_image) {
            return null;
        }

        $thumbnailPath = $this->custom_properties['thumbnail_path'] ?? null;
        
        return $thumbnailPath 
            ? Storage::disk($this->disk)->url($thumbnailPath)
            : $this->url;
    }

     // Scopes
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('uploaded_by', $userId);
    }

    public function scopeOnDisk($query, $disk)
    {
        return $query->where('disk', $disk);
    }

    public static function findByHash($hash)
    {
        return static::where('hash', $hash)->first();
    }

    public function getResponsiveImageUrls(): array
    {
        if (!$this->is_image) {
            return [];
        }

        $sizes = [
            'thumbnail' => 300,
            'medium' => 800,
            'large' => 1200
        ];

        $urls = [];
        foreach ($sizes as $name => $width) {
            $urls[$name] = $this->getResizedImageUrl($width);
        }

        return $urls;
    }

     protected function getResizedImageUrl(int $width): string
    {
        return $this->url . "?w={$width}";
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            if ($attachment->isForceDeleting()) {
                Storage::disk($attachment->disk)->delete($attachment->path);
                
                if ($attachment->is_image && isset($attachment->custom_properties['thumbnail_path'])) {
                    Storage::disk($attachment->disk)->delete(
                        $attachment->custom_properties['thumbnail_path']
                    );
                }
            }
        });
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'cover_image_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'main_image_id');
    }

    public function productAttachments(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attachments')
                   ->withPivot('type', 'order')
                   ->withTimestamps();
    }

    public function scopeVideos($query)
    {
        return $query->where('mime_type', 'like', 'video/%');
    }

    public function getDimensionAttribute(): ?string
    {
        if ($this->width && $this->height) {
            return "{$this->width}x{$this->height}";
        }
        return null;
    }

    public function getDurationFormattedAttribute(): ?string
    {
        if (!$this->duration) {
            return null;
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeLargeFiles($query, int $size = 5000) // 5MB
    {
        return $query->where('size', '>', $size * 1024);
    }
}