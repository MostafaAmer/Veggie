<?php
namespace App\Factories;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentMethod;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use App\Services\Gateways\StripeGateway;
use App\Services\Gateways\WalletGateway;
use App\Services\Gateways\CodGateway;

class GatewayFactory
{
    public function __construct(private Container $app) {}

    public function make(PaymentMethod $method): PaymentGatewayInterface
    {
        return match ($method) {
            PaymentMethod::CreditCard   => $this->app->make(StripeGateway::class),
            PaymentMethod::Wallet,
            PaymentMethod::BankTransfer    => $this->app->make(WalletGateway::class),
            PaymentMethod::CashOnDelivery => $this->app->make(CodGateway::class),
            default => throw new InvalidArgumentException('Unsupported payment method'),
        };
    }
}