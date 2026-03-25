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
            'collections' => Collection::query()
                ->get()
                ->map(fn (Collection $c): array => ['id' => (int) $c->id, 'name' => $c->translateAttribute('name')]),
        ]);
    }

    public function show(string $slug): Response
    {
        $product = $this->getProduct->handle($slug);

        return Inertia::render('products/show', [
            'product' => [
                'id' => (int) $product->id,
                'name' => $product->translateAttribute('name'),
                'price' => data_get($product, 'variants.0.prices.0.price.value'),
                'description' => $product->translateAttribute('description'),
                'mainImage' => $product->getFirstMediaUrl('images') ?: null,
            ],
        ]);
    }
}
