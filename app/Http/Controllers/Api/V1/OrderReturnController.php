<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReturnOrderRequest;
use App\Http\Resources\OrderReturnResource;
use App\Models\Order;
use App\Services\OrderReturnService;
use Illuminate\Http\JsonResponse;
use App\Models\OrderReturn;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderReturnController extends Controller
{
    public function __construct(private OrderReturnService $returnService)
    {
        $this->middleware('auth:sanctum');
    }

    public function return(ReturnOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('return', $order);
        
        $orderReturn = $this->returnService->createReturn(
            $order,
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'تم تقديم طلب الإرجاع بنجاح',
            'return' => new OrderReturnResource($orderReturn)
        ], 201);
    }

    public function approve(OrderReturn $return): JsonResponse
    {
        $this->authorize('approve', $return);
        
        $approvedReturn = $this->returnService->approveReturn(
            $return,
            auth()->user()
        );

        return response()->json([
            'message' => 'تم الموافقة على طلب الإرجاع',
            'return' => new OrderReturnResource($approvedReturn)
        ]);
    }

    public function reject(Request $request, OrderReturn $return): JsonResponse
    {
        $this->authorize('reject', $return);

        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $rejected = $this->returnService->rejectReturn(
            $return,
            $request->input('reason'),
            $request->user()
        );

        return response()->json([
            'message' => 'تم رفض طلب الإرجاع',
            'return'  => new OrderReturnResource($rejected),
        ]);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = OrderReturn::with(['order', 'user', 'items']);

        if ($request->user()->cannot('viewAny', OrderReturn::class)) {
            $query->where('user_id', $request->user()->id);
        }

        $returns = $query->latest()->paginate();

        return OrderReturnResource::collection($returns);
    }

    public function show(OrderReturn $return): OrderReturnResource
    {
        $this->authorize('view', $return);

        $return->load(['order', 'items.orderItem.product', 'approvedBy', 'rejectedBy', 'refundedBy']);

        return new OrderReturnResource($return);
    }

    public function markAsRefunded(OrderReturn $return): JsonResponse
    {
        $this->authorize('refund', $return);
        
        $refundedReturn = $this->returnService->markAsRefunded(
            $return,
            auth()->user()
        );
        
        return response()->json([
            'message' => 'تم استرداد المبلغ بنجاح',
            'return' => new OrderReturnResource($refundedReturn)
        ]);
    }
}