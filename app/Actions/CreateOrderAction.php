<?php

declare(strict_types=1);

namespace App\Actions;

use Lunar\Facades\CartSession;
use Lunar\Models\Order;

final readonly class CreateOrderAction
{
    public function handle(): Order
    {
        return CartSession::createOrder();
    }
}
