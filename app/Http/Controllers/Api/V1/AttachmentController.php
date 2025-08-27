<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Resources\AttachmentResource;
use App\Models\Attachment;
use App\Services\AttachmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function __construct(
        private AttachmentService $attachmentService
    ) {
        $this->middleware('auth:sanctum')->except(['show']);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $attachments = $this->attachmentService
            ->getUserAttachments($request->user())
            ->with('uploader:id,name')
            ->paginate(20);

        return AttachmentResource::collection($attachments);
    }

    public function store(StoreAttachmentRequest $request): JsonResponse
    {
        $attachment = $this->attachmentService->uploadAttachment(
            $request->file('file'),
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Attachment uploaded successfully',
            'attachment' => new AttachmentResource($attachment)
        ], 201);
    }

    public function show(Attachment $attachment): AttachmentResource
    {
        $this->authorize('view', $attachment);
        return new AttachmentResource($attachment);
    }

    public function destroy(Attachment $attachment): JsonResponse
    {
        $this->authorize('delete', $attachment);
        $this->attachmentService->deleteAttachment($attachment);

        return response()->json([
            'message' => 'The attachment has been successfully deleted.'
        ]);
    }

    public function download(Attachment $attachment)
    {
        $this->authorize('download', $attachment);
        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    public function restore($id): JsonResponse
    {
        $attachment = Attachment::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $attachment);
        $attachment->restore();

        return response()->json(['message' => 'The attachment was successfully restored.']);
    }

    public function forceDelete($id): JsonResponse
    {
        $attachment = Attachment::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $attachment);
        $this->attachmentService->deleteAttachment($attachment, true);

        return response()->json(['message' => 'The attachment has been permanently deleted.']);
    }
}