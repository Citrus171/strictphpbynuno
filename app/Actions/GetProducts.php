<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

final readonly class GetProducts
{
    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function handle(
        int $perPage = 12,
        int $page = 1,
        ?int $brand = null,
        ?int $collection = null,
        ?string $sort = null,
        ?string $search = null,
    ): LengthAwarePaginator {
        return Product::query()
            ->status('published')
            ->with(['brand', 'variants.prices', 'media', 'defaultUrl'])
            ->when($search !== null, function (Builder $query) use ($search): void {
                $query->whereRaw($this->nameAttributeExpression($query).' LIKE ?', [sprintf('%%%s%%', $search)]);
            })
            ->when($brand !== null, fn (Builder $query) => $query->where('brand_id', $brand))
            ->when($collection !== null, fn (Builder $query) => $query->whereHas('collections', fn (Builder $q) => $q->where('lunar_collections.id', $collection)))
            ->when($sort === 'name_asc', function (Builder $query): void {
                $query->orderByRaw($this->nameAttributeExpression($query).' ASC');
            })
            ->when(in_array($sort, ['price_asc', 'price_desc'], true), function (Builder $query) use ($sort): void {
                $direction = $sort === 'price_asc' ? 'ASC' : 'DESC';
                $morphType = ProductVariant::morphName();
                $query->orderByRaw(
                    '(SELECT MIN(p.price) FROM lunar_prices p
                      JOIN lunar_product_variants v ON p.priceable_id = v.id
                      WHERE p.priceable_type = ? AND v.product_id = lunar_products.id) '.$direction,
                    [$morphType]
                );
            })
            ->when(! in_array($sort, ['price_asc', 'price_desc', 'name_asc'], true), fn (Builder $query) => $query->latest())
            ->paginate(perPage: $perPage, page: $page);
    }

    /**
     * @codeCoverageIgnore
     */
    private function nameAttributeExpression(Builder $query): string
    {
        return $query->getConnection()->getDriverName() === 'mysql'
            ? "JSON_UNQUOTE(JSON_EXTRACT(attribute_data, '$.name.value'))"
            : "json_extract(attribute_data, '$.name.value')";
    }
}
