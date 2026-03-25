<?php

declare(strict_types=1);

namespace App\Actions;

use Lunar\Facades\CartSession;
use Lunar\Models\Cart;

final readonly class UpdateCartItem
{
    public function handle(int $cartLineId, int $quantity): Cart
    {
        /** @var Cart $cart */
        $cart = CartSession::manager();

        return $cart->updateLine($cartLineId, $quantity);
    }
}
