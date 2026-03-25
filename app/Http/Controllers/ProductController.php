<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetProducts;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Models\Product;

final readonly class ProductController
{
    public function __construct(private GetProducts $getProducts)
    {
        //
    }

    public function index(Request $request): Response
    {
        $perPage = (int) $request->integer('perPage', 12);
        $page = (int) $request->integer('page', 1);

        $products = $this->getProducts->handle(perPage: $perPage, page: $page);

        return Inertia::render('products/index', [
            'products' => $products->through(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->translateAttribute('name'),
                'brand' => $product->brand?->name,
                'price' => $product->variants->first()?->prices->first()?->price->value,
                'thumbnail' => $product->thumbnail?->getUrl('small') ?: null,
            ]),
        ]);
    }
}
