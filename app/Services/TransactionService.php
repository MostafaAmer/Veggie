<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\{TransactionStatus, TransactionAction};
use Illuminate\Support\Carbon;

class TransactionService
{
    public function approveTransaction(Transaction $transaction, User $user): Transaction
    {
        return DB::transaction(function () use ($transaction, $user) {
            $transaction->update([
                'status' => TransactionStatus::Completed->value,
                'processed_by_id' => $user->id,
                'processed_at' => Carbon::now()
            ]);

            $this->logTransactionStatus(
                transaction: $transaction,
                action:      TransactionAction::Approved,
                status:      TransactionStatus::Completed,
                details:     "Approved by user #{$user->id}",
                user:        $user
            );

            Log::info("Transaction [{$transaction->id}] approved by user [{$user->id}]");

            return $transaction;
        });
    }

    public function cancelTransaction(Transaction $transaction, User $user, ?string $reason = null): Transaction
    {
        return DB::transaction(function () use ($transaction, $user, $reason) {
            $transaction->update([
                'status' => TransactionStatus::Cancelled->value,
                'processed_by' => $user->id,
                'processed_at' => Carbon::now(),
                'notes' => $reason
            ]);

            $this->logTransactionStatus(
                transaction: $transaction,
                action:      TransactionAction::Cancelled,
                status:      TransactionStatus::Cancelled,
                details:     $reason ?? "Cancelled by user #{$user->id}",
                user:        $user
            );

            Log::warning("Transaction [{$transaction->id}] cancelled by user [{$user->id}]: {$reason}");

            return $transaction;
        });
    }

    public function updateTransaction(Transaction $transaction, array $data, User $updatedBy): Transaction
    {
        return DB::transaction(function () use ($transaction, $data, $updatedBy) {
            $original = $transaction->getOriginal();
            $metadata = array_merge(
                $transaction->metadata ?? [],
                ['updated_by' => $user->id, 'updated_at' => now()->toDateTimeString()]
            );

            $transaction->update(array_merge($data, ['metadata' => $metadata]));

            $changes = collect($transaction->getChanges())
                ->except(['updated_at', 'metadata'])
                ->mapWithKeys(fn($value, $key) => [
                    $key => [
                        'from' => $originalData[$key] ?? null,
                        'to' => $new
                    ]
                ])->toArray();

            $this->logTransactionStatus(
                transaction: $transaction,
                action:      TransactionAction::Updated,
                status:      $transaction->status,
                details:     'Transaction updated',
                user:        $user,
                extra:       ['changes' => $changes, 'notes' => $data['notes'] ?? null]
            );

            return $transaction;
        });
    }

    public function updateMetadata(Transaction $transaction, array $metadata, User $user): Transaction
    {
        $merged = array_merge($transaction->metadata ?? [], $metadata);
        $transaction->update([
            'metadata' => $merged,
            'updated_at' => Carbon::now()
        ]);

        $this->logTransactionStatus(
           transaction: $transaction,
            action:      TransactionAction::MetadataUpdated,
            status:      $transaction->status,
            details:     'Metadata updated',
            user:        $user
        );

        return $transaction;
    }

    public function logTransactionStatus(Transaction $transaction, TransactionAction $action, TransactionStatus $status, string $details, User $user, array $extra = []): void
    {
        $payload = array_merge([
            'action'     => $action->value,
            'status'     => $status->value,
            'details'    => $details,
            'changed_by' => $user->id,
            'changed_at' => now(),
        ], $extra);

        $transaction->statusLogs()->create($payload);

        Log::info("Status log for txn [{$transaction->id}] by user [{$user->id}]: {$details}");
    }

    

    public function getTransactionStats(array $filters, User $user): array
    {
        $base = Transaction::query()
            ->when(!$user->can('manage_transactions'), fn($q) => $q->where('user_id', $user->id))
            ->when($filters['date_from'] ?? false, fn($q, $d) => $q->where('created_at', '>=', $d))
            ->when($filters['date_to'] ?? false,   fn($q, $d) => $q->where('created_at', '<=', $d));

        $total          = (clone $base)->count();
        $totalAmount    = (clone $base)->sum('amount');
        $completedCount = (clone $base)->where('status', TransactionStatus::Completed->value)->count();
        $pendingCount   = (clone $base)->where('status', TransactionStatus::Pending->value)->count();
        $failedCount    = (clone $base)->where('status', TransactionStatus::Failed->value)->count();

        $byType = (clone $base)
            ->selectRaw('type, count(*) as count, sum(amount) as total_amount')
            ->groupBy('type')
            ->get()
            ->mapWithKeys(fn($item) => [
                $item->type => [
                    'count'        => $item->count,
                    'total_amount' => $item->total_amount,
                ]
            ]);
        $byMethod = (clone $base)
            ->selectRaw('payment_method, count(*) as count, sum(amount) as total_amount')
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(fn($item) => [
                $item->payment_method => [
                    'count'        => $item->count,
                    'total_amount' => $item->total_amount,
                ]
            ]);
        return [
            'total'             => $total,
            'total_amount'      => $totalAmount,
            'completed'         => $completedCount,
            'pending'           => $pendingCount,
            'failed'            => $failedCount,
            'by_type'           => $byType,
            'by_payment_method' => $byMethod,
        ];
    }

    public function getFilteredTransactions(array $filters, User $user): Paginator
    {
        return Transaction::query()
            ->with(['order:id,reference_number', 'user:id,name,email'])
            ->when(!$user->can('manage_transactions'), fn($q) => $q->where('user_id', $user->id))
            ->when($filters['type'] ?? false,             fn($q, $v) => $q->where('type', $v))
            ->when($filters['status'] ?? false,           fn($q, $v) => $q->where('status', $v))
            ->when($filters['payment_method'] ?? false,   fn($q, $v) => $q->where('payment_method', $v))
            ->when($filters['gateway'] ?? false,          fn($q, $v) => $q->where('gateway', $v))
            ->when($filters['date_from'] ?? false,        fn($q, $v) => $q->where('created_at', '>=', $v))
            ->when($filters['date_to'] ?? false,          fn($q, $v) => $q->where('created_at', '<=', $v))
            ->when($filters['amount_min'] ?? false,       fn($q, $v) => $q->where('amount', '>=', $v))
            ->when($filters['amount_max'] ?? false,       fn($q, $v) => $q->where('amount', '<=', $v))
            ->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();
    }

    public function getTransactionDetails(Transaction $transaction): Transaction
    {
        return $transaction->load([
            'order.items.product:id,name,image',
            'user:id,name,email,phone',
            'processedBy:id,name',
            'statusLogs.changedBy:id,name',
        ]);
    }
}