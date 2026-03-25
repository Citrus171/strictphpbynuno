<?php

declare(strict_types=1);

namespace App\Shipping;

use Closure;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;

final class FlatRateShipping
{
    /**
     * 送料オプションをマニフェストに追加する。
     */
    public function handle(Cart $cart, Closure $next): Cart
    {
        $currency = Currency::query()->where('default', true)->first();
        $taxClass = TaxClass::query()->first();

        if (! $currency || ! $taxClass) {
            return $next($cart);
        }

        ShippingManifest::addOption(new ShippingOption(
            name: '標準配送',
            description: '通常2〜5営業日でお届け',
            identifier: 'flat_rate_standard',
            price: new Price(500, $currency),
            taxClass: $taxClass,
        ));

        ShippingManifest::addOption(new ShippingOption(
            name: '速達便',
            description: '翌営業日お届け',
            identifier: 'flat_rate_express',
            price: new Price(1200, $currency),
            taxClass: $taxClass,
        ));

        return $next($cart);
    }
}
