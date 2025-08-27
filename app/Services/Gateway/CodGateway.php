<?php
namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;

class CodGateway implements PaymentGatewayInterface
{
    public function charge(Payment $payment, array $data): array
    {
        // No external call; COD is accepted by default
        return ['message' => 'سيتم الدفع عند الاستلام'];
    }

    public function refund(Payment $payment, float $amount): array
    {
        // Usually handled offline
        return [];
    }
}