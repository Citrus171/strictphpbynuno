<?php

declare(strict_types=1);

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Lunar\DataTypes\Price;

/**
 * @property int $id
 * @property ?int $customer_id
 * @property ?int $user_id
 * @property int $channel_id
 * @property bool $new_customer
 * @property string $status
 * @property ?string $reference
 * @property ?string $customer_reference
 * @property Price $sub_total
 * @property int $discount_total
 * @property array<mixed> $discount_breakdown
 * @property array<mixed> $shipping_breakdown
 * @property array<mixed> $tax_breakdown
 * @property int $tax_total
 * @property Price $total
 * @property ?string $notes
 * @property string $currency_code
 * @property ?string $compare_currency_code
 * @property float $exchange_rate
 * @property ?Carbon $placed_at
 * @property ?array<mixed> $meta
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
final class Order
{
    /** @return HasMany<OrderLine> */
    public function lines(): HasMany {}
}
