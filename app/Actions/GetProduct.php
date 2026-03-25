<?php

declare(strict_types=1);

namespace App\Actions;

use Lunar\Models\Product;

final readonly class GetProduct
{
    public function handle(string $slug): Product
    {
        return Product::query()
            ->status('published')
            ->with(['brand', 'variants.prices', 'media', 'defaultUrl'])
            ->whereHas('urls', function ($query) use ($slug): void {
                $query->where('slug', $slug)->where('default', true);
            })
            ->firstOrFail();
    }
}
