<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Models\Product;

final readonly class GetProduct
{
    public function handle(string $slug): Product
    {
        return Product::query()
            ->status('published')
            ->with([
                'brand',
                'variants.prices',
                'variants.values.option',
                'media',
                'defaultUrl',
                'associations.target.variants.prices',
                'associations.target.media',
                'associations.target.defaultUrl',
            ])
            ->whereHas('urls', function (Builder $query) use ($slug): void {
                $query->where('slug', $slug)->where('default', true);
            })
            ->firstOrFail();
    }
}
