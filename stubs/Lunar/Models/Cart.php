<?php

declare(strict_types=1);

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

final class Cart
{
    /** @return HasMany<CartLine> */
    public function lines(): HasMany {}
}
