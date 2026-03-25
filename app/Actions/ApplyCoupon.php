<?php

declare(strict_types=1);

namespace App\Actions;

use Lunar\Models\Cart;
use Lunar\Models\Discount;

final readonly class ApplyCoupon
{
    /**
     * カートにクーポンコードを適用する。
     *
     * @return bool クーポンが有効で適用された場合は true、無効な場合は false
     */
    public function handle(Cart $cart, string $couponCode): bool
    {
        $exists = Discount::query()
            ->active()
            ->usable()
            ->where('coupon', mb_strtoupper($couponCode))
            ->exists();

        if (! $exists) {
            return false;
        }

        $cart->coupon_code = $couponCode;
        $cart->save();

        return true;
    }
}
