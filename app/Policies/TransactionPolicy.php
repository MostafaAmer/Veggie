<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use App\Enums\TransactionStatus;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
       return $user->hasPermission('transaction.viewAny');
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id || $user->hasPermission('transaction.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('transaction.create');
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $user->can('manage_transactions') &&
               in_array($transaction->status, [
                   TransactionStatus::Pending,
                   TransactionStatus::RequiresAction,
                   TransactionStatus::OnHold
               ]);
    }

    public function approve(User $user, Transaction $transaction): bool
    {
        return $user->can('approve_transactions') &&
               $transaction->status === TransactionStatus::Pending;
    }

   public function cancel(User $user, Transaction $transaction): bool
    {
        return $user->can('manage_transactions') &&
               in_array($transaction->status, [
                   TransactionStatus::Pending,
                   TransactionStatus::RequiresAction
               ]);
    }

    public function viewStats(User $user): bool
    {
        return $user->can('view_transaction_stats');
    }

    public function viewGatewayResponse(User $user): bool
    {
        return $user->can('view_gateway_response');
    }

    public function viewTransactionMetadata(User $user): bool
    {
        return $user->can('view_transaction_metadata');
    }
}