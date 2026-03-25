<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetProduct;
use App\Actions\GetProducts;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Models\Product;

final readonly class ProductController
{
    public function __construct(private GetProducts $getProducts, private GetProduct $getProduct) {}

    public function index(Request $request): Response
    {
        $perPage = (int) $request->integer('perPage', 12);
        $page = (int) $request->integer('page', 1);

        $products = $this->getProducts->handle(perPage: $perPage, page: $page);

        return Inertia::render('products/index', [
            'products' => $products->through(fn (Product $product): array => [
                'id' => (int) $product->id,
                'slug' => data_get($product, 'defaultUrl.slug'),
                'name' => $product->translateAttribute('name'),
                'brand' => data_get($product, 'brand.name'),
                'price' => data_get($product, 'variants.0.prices.0.price.value'),
                'thumbnail' => $product->getFirstMediaUrl('images', 'small') ?: null,
            ]),
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
