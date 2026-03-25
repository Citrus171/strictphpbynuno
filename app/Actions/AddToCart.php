<?php

declare(strict_types=1);

namespace App\Actions;

use Lunar\Facades\CartSession;
use Lunar\Models\Cart;
use Lunar\Models\ProductVariant;

final readonly class AddToCart
{
    public function handle(int $variantId, int $quantity = 1): Cart
    {
        $variant = ProductVariant::query()->findOrFail($variantId);

        /** @var Cart $cart */
        $cart = CartSession::manager();

        return $cart->add($variant, $quantity);
    }
}
