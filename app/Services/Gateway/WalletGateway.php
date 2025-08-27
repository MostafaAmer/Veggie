<?php
namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use App\Exceptions\PaymentFailedException;
use App\Exceptions\RefundFailedException;

class WalletGateway implements PaymentGatewayInterface
{
    public function charge(Payment $payment, array $data): array
    {
        if (empty($data['transaction_id'])) {
            throw new PaymentFailedException('Missing wallet transaction_id');
        }

        // You might verify the transaction via provider API here...
        return ['transaction_id' => $data['transaction_id']];
    }

    public function refund(Payment $payment, float $amount): array
    {
        // Cash-style refund: record it locally
        return [];
    }
}