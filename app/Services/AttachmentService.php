<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService
{
    public function uploadAttachment(UploadedFile $file, $user, array $meta = []): Attachment
    {
        $uuid = Str::uuid()->toString();
        $disk = $meta['disk'] ?? 'public';
        $path = $file->storeAs("attachments/{$user->id}", $uuid . '.' . $file->getClientOriginalExtension(), $disk);

        $attachment = Attachment::create([
            'id'                => $uuid,
            'original_name'     => $file->getClientOriginalName(),
            'path'              => $path,
            'mime_type'         => $file->getClientMimeType(),
            'file_size'         => $file->getSize(),
            'uploaded_by'       => $user->id,
            'disk'              => $disk,
            'type'              => $meta['type'] ?? 'image',
            'alt_text'          => $meta['alt_text'] ?? null,
            'custom_properties' => $meta['custom_properties'] ?? null,
            'hash'              => md5_file($file->getRealPath()),
            'width'             => $meta['width'] ?? null,
            'height'            => $meta['height'] ?? null,
            'duration'          => $meta['duration'] ?? null,
        ]);

        return $attachment;
    }

    public function deleteAttachment(Attachment $attachment, bool $force = false): void
    {
        if ($force) {
            $attachment->forceDelete();
            Storage::disk($attachment->disk)->delete($attachment->path);
            if ($attachment->is_image && isset($attachment->custom_properties['thumbnail_path'])) {
                Storage::disk($attachment->disk)
                    ->delete($attachment->custom_properties['thumbnail_path']);
            }
        } else {
            $attachment->delete();
        }
    }
}