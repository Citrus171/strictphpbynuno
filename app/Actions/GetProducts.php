<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Pagination\LengthAwarePaginator;
use Lunar\Models\Product;

final readonly class GetProducts
{
    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function handle(int $perPage = 12, int $page = 1): LengthAwarePaginator
    {
        return Product::query()
            ->status('published')
            ->with(['brand', 'variants.prices', 'media'])
            ->latest()
            ->paginate(perPage: $perPage, page: $page);
    }
}
