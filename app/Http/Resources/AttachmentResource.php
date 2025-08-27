<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AttachmentResource extends JsonResource
{
    public function toArray($request)
    {
        $cdnUrl = config('app.cdn_url');
        $publicUrl = Storage::disk($this->disk)->url($this->path);
        $url = $cdnUrl && $this->disk === 'public'
            ? str_replace(Storage::disk('public')->url(''), $cdnUrl, $publicUrl)
            : $publicUrl;

        return [
            'id'            => $this->id,
            'name'          => $this->original_name,
            'url'           => $url,
            'type'          => $this->type,
            'mime_type'     => $this->mime_type,
            'size'          => $this->file_size,
            'is_image'      => $this->is_image,
            'dimensions'    => $this->is_image
                ? ['width' => $this->width, 'height' => $this->height]
                : null,
            'thumbnail_url' => $this->thumbnail_url,
            'uploaded_by'   => [
                'id'   => $this->uploader->id,
                'name' => $this->uploader->name,
            ],
            'created_at'    => $this->created_at->toIso8601String(),
            'updated_at'    => $this->updated_at->toIso8601String(),
            'links'         => [
                'download' => route('api.v1.attachments.download', $this->id),
                'delete'   => route('api.v1.attachments.destroy',  $this->id),
            ],
        ];
    }
}