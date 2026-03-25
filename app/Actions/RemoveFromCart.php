<?php

declare(strict_types=1);

namespace App\Actions;

use Lunar\Facades\CartSession;
use Lunar\Models\Cart;

final readonly class RemoveFromCart
{
    public function handle(int $cartLineId): Cart
    {
        /** @var Cart $cart */
        $cart = CartSession::manager();

        return $cart->remove($cartLineId);
    }
}
