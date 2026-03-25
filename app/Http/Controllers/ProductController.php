<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetProduct;
use App\Actions\GetProducts;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Models\Brand;
use Lunar\Models\Collection;
use Lunar\Models\Product;
use Lunar\Models\ProductAssociation;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;
use Lunar\Models\ProductVariant;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final readonly class ProductController
{
    public function __construct(private GetProducts $getProducts, private GetProduct $getProduct) {}

    public function index(Request $request): Response
    {
        $perPage = $request->integer('perPage', 12);
        $page = $request->integer('page', 1);
        $brand = $request->integer('brand') ?: null;
        $collection = $request->integer('collection') ?: null;
        $sort = $request->string('sort')->value() ?: null;
        $search = $request->string('search')->value() ?: null;

        $products = $this->getProducts->handle(
            perPage: $perPage,
            page: $page,
            brand: $brand,
            collection: $collection,
            sort: $sort,
            search: $search,
        );

        /** @var \Illuminate\Database\Eloquent\Collection<int, Collection> $collections */
        $collections = Collection::query()->get();

        return Inertia::render('products/index', [
            'products' => $products->through(fn (Product $product): array => [
                'id' => (int) $product->id,
                'slug' => data_get($product, 'defaultUrl.slug'),
                'name' => $product->translateAttribute('name'),
                'brand' => data_get($product, 'brand.name'),
                'price' => data_get($product, 'variants.0.prices.0.price.value'),
                'thumbnail' => $product->getFirstMediaUrl('images', 'small') ?: null,
            ]),
            'filters' => [
                'brand' => $brand,
                'collection' => $collection,
                'sort' => $sort,
                'search' => $search,
            ],
            'brands' => Brand::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Brand $b): array => ['id' => (int) $b->id, 'name' => $b->name]),
            'collections' => $collections->map(fn (Collection $c): array => ['id' => (int) $c->id, 'name' => $c->translateAttribute('name')]),
        ]);
    }

    public function show(string $slug): Response
    {
        $product = $this->getProduct->handle($slug);

        /** @var \Illuminate\Database\Eloquent\Collection<int, ProductVariant> $variants */
        $variants = $product->variants;

        /** @var \Illuminate\Database\Eloquent\Collection<int, ProductAssociation> $associations */
        $associations = $product->associations;

        return Inertia::render('products/show', [
            'product' => [
                'id' => (int) $product->id,
                'name' => $product->translateAttribute('name'),
                'price' => data_get($product, 'variants.0.prices.0.price.value'),
                'description' => $product->translateAttribute('description'),
                'mainImage' => $product->getFirstMediaUrl('images') ?: null,
                'images' => $product->getMedia('images')->map(fn (Media $media): array => [
                    'url' => $media->getUrl() ?: null,
                    'thumbnail' => $media->getUrl('small') ?: $media->getUrl() ?: null,
                ])->values()->all(),
                'variants' => $variants->map(function (ProductVariant $variant): array {
                    /** @var \Illuminate\Database\Eloquent\Collection<int, ProductOptionValue> $values */
                    $values = $variant->values;

                    return [
                        'id' => (int) $variant->id,
                        'sku' => $variant->sku,
                        'price' => data_get($variant, 'prices.0.price.value'),
                        'stock' => $variant->stock,
                        'inStock' => match ($variant->purchasable) {
                            'always', 'backorder' => true,
                            'in_stock' => $variant->stock > 0,
                            default => false,
                        },
                        'options' => $values->map(function (ProductOptionValue $value): array {
                            /** @var ProductOption $option */
                            $option = $value->option;

                            return [
                                'name' => $option->translate('name'),
                                'value' => $value->translate('name'),
                            ];
                        })->values()->all(),
                    ];
                })->values()->all(),
                'relatedProducts' => $associations->map(function (ProductAssociation $assoc): array {
                    /** @var Product $target */
                    $target = $assoc->target;

                    return [
                        'id' => (int) $target->id,
                        'name' => $target->translateAttribute('name'),
                        'price' => data_get($target, 'variants.0.prices.0.price.value'),
                        'thumbnail' => $target->getFirstMediaUrl('images', 'small') ?: null,
                        'slug' => data_get($target, 'defaultUrl.slug'),
                    ];
                })->values()->all(),
            ],
        ]);
    }
}
