<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\{
    TransactionFilterRequest,
    UpdateTransactionRequest,
    ApproveTransactionRequest,
    CancelTransactionRequest
};
use App\Services\TransactionService;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {
        $this->middleware('auth:sanctum');
        $this->authorizeResource(Transaction::class, 'transaction');
    }

    public function index(TransactionFilterRequest $request): JsonResponse
    {
        $user         = $request->user();
        $filters      = $request->validated();
        $transactions = $this->transactionService->getFilteredTransactions($filters, $user);


        return TransactionResource::collection($transactions)
            ->response()
            ->setStatusCode(200);
    }

    public function show(Transaction $transaction): JsonResponse
    {
        $transaction = $this->transactionService->getTransactionDetails($transaction);

        return TransactionResource::make($transaction)
            ->response()
            ->setStatusCode(200);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $user       = $request->user();
        $attributes = $request->validated();

        $updated = $this->transactionService->updateTransaction(
            $transaction,
            $attributes,
            $user
        );

        return response()->json([
            'message'     => 'تم تحديث المعاملة بنجاح',
            'transaction' => new TransactionResource($updated),
        ], 200);
    }

    public function approve(ApproveTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $user     = $request->user();
        $approved = $this->transactionService->approveTransaction($transaction, $user);

        return response()->json([
            'message'     => 'تمت الموافقة على المعاملة بنجاح',
            'transaction' => new TransactionResource($approved),
        ], 200);
    }

    public function cancel(CancelTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $user   = $request->user();
        $reason = $request->validated()['reason'] ?? null;

        $cancelled = $this->transactionService->cancelTransaction(
            $transaction,
            $user,
            $reason
        );

        return response()->json([
            'message'     => 'تم إلغاء المعاملة بنجاح',
            'transaction' => new TransactionResource($cancelled),
        ], 200);
    }

    public function stats(TransactionFilterRequest $request): JsonResponse
    {
        $user    = $request->user();
        $filters = $request->validated();
        $stats   = $this->transactionService->getTransactionStats($filters, $user);

        return response()->json($stats, 200);
    }
}