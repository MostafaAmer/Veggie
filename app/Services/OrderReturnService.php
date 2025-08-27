<?php

namespace App\Services;

use App\Models\{OrderReturn, Order, Transaction, User};
use Illuminate\Support\Facades\DB;
use App\Enums\{OrderReturnStatus , RefundMethod, ReturnItemCondition};
use App\Events\{OrderReturnCreated, OrderReturnApproved, OrderReturnRejected, OrderReturnRefunded};


class OrderReturnService
{
    public function createReturn(Order $order, array $data, User $user): OrderReturn
    {
        return DB::transaction(function () use ($order, $data, $user) {
            $return = $order->returns()->create([
                'user_id'       => $user->id,
                'status'        => OrderReturnStatus::Pending,
                'reason'        => $data['reason'],
                'refund_method' => $data['refund_method'] ?? RefundMethod::OriginalMethod->value,
                'bank_details'  => $data['bank_details']  ?? null,
            ]);

            foreach ($data['items'] as $row) {
                $return->items()->create([
                    'order_item_id' => $row['order_item_id'],
                    'quantity'      => $row['quantity'],
                    'condition'     => $row['condition'] ?? ReturnItemCondition::Unopened->value,
                ]);
            }

            if (! empty($data['attachments'])) {
                $this->processAttachments($return, $data['attachments']);
            }

            event(new OrderReturnCreated($return));
            return $return->load('items');
        });
    }

    public function approveReturn(OrderReturn $return, User $approver): OrderReturn
    {
        return DB::transaction(function () use ($return, $approver) {
            $return->update([
                'status'      => OrderReturnStatus::Approved,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            $this->restockItems($return);

            if ($return->order->is_paid) {
                $this->processRefund($return);
            }

            event(new OrderReturnApproved($return));
            return $return->fresh('items');
        });
    }

    public function rejectReturn(OrderReturn $return, string $reason, User $rejectedBy): OrderReturn
    {
        return DB::transaction(function () use ($return, $reason, $rejectedBy) {
            $return->update([
                'status'          => OrderReturnStatus::Rejected,
                'rejected_by'     => $rejectedBy->id,
                'rejected_reason' => $reason,
                'rejected_at'     => now(),
            ]);

            event(new OrderReturnRejected($return));
            return $return;
        });
    }

    protected function processRefund(OrderReturn $return): void
    {
        $amount = $this->calculateRefundAmount($return);
        $return->update(['refund_amount' => $amount]);

        match ($return->refund_method) {
            RefundMethod::BankTransfer    => $this->processBankTransferRefund($return),
            RefundMethod::WalletCredit    => $this->processWalletCreditRefund($return),
            default                        => $this->processOriginalMethodRefund($return),
        };

        event(new OrderReturnRefunded($return));
    }

    protected function processOriginalMethodRefund(OrderReturn $return): void
    {
        Transaction::create([
            'order_id' => $return->order_id,
            'user_id'  => $return->user_id,
            'amount'   => $return->refund_amount,
            'type'     => 'refund',
            'status'   => 'pending',
            'notes'    => "Refund via original for return #{$return->id}",
        ]);
    }

    protected function processWalletCreditRefund(OrderReturn $return): void
    {
        Transaction::create([
            'order_id' => $return->order_id,
            'user_id'  => $return->user_id,
            'amount'   => $return->refund_amount,
            'type'     => 'wallet_credit',
            'status'   => 'completed',
            'notes'    => "Wallet credit for return #{$return->id}",
        ]);
    }

    protected function processBankTransferRefund(OrderReturn $return): void
    {
        Transaction::create([
            'order_id' => $return->order_id,
            'user_id'  => $return->user_id,
            'amount'   => $return->refund_amount,
            'type'     => 'bank_transfer',
            'status'   => 'processing',
            'details'  => $return->bank_details,
            'notes'    => "Bank transfer for return #{$return->id}",
        ]);
    }

    public function calculateRefundAmount(OrderReturn $return): float
    {
        $total = 0;
        foreach ($return->items as $item) {
            $price = $item->orderItem->price;
            $total += match ($item->condition->value) {
                'unopened' => $price * $item->quantity,
                'opened'   => ($price * 0.7) * $item->quantity,
                'damaged'  => ($price * 0.3) * $item->quantity,
                default    => 0,
            };
        }
        return max(0, round($total, 2));
    }

    protected function processAttachments(OrderReturn $return, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store("returns/{$return->id}", 'public');
            $return->attachments()->create([
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
            ]);
        }
    }

    protected function restockItems(OrderReturn $return): void
    {
        foreach ($return->items as $item) {
            if ($item->condition === 'unopened') {
                $item->orderItem->product->increment('stock', $item->quantity);
                $item->update(['restocked' => true]);
            }
        }
    }

    public function markAsRefunded(OrderReturn $return, User $processor): OrderReturn
    {
        return DB::transaction(function () use ($return, $processor) {
            $return->transactions()
                   ->where('status', 'pending')
                   ->update(['status' => 'completed']);

            $return->update([
                'status'      => OrderReturnStatus::Refunded,
                'refunded_by'=> $processor->id,
                'refunded_at'=> now(),
            ]);

            event(new OrderReturnRefunded($return));
            return $return->fresh();
        });
    }

}