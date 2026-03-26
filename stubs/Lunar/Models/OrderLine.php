<?php

declare(strict_types=1);

namespace Lunar\Models;

use Illuminate\Support\Carbon;
use Lunar\DataTypes\Price;

/**
 * @property int $id
 * @property int $order_id
 * @property string $purchasable_type
 * @property int $purchasable_id
 * @property string $type
 * @property string $description
 * @property ?string $option
 * @property string $identifier
 * @property int $unit_price
 * @property int $unit_quantity
 * @property int $quantity
 * @property Price $sub_total
 * @property int $discount_total
 * @property array<mixed> $tax_breakdown
 * @property int $tax_total
 * @property int $total
 * @property ?string $notes
 * @property ?array<mixed> $meta
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
final class OrderLine {}
