<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AddToCart;
use App\Actions\RemoveFromCart;
use App\Actions\UpdateCartItem;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Facades\CartSession;
use Lunar\Models\CartLine;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

final readonly class CartController
{
    public function __construct(
        private AddToCart $addToCart,
        private UpdateCartItem $updateCartItem,
        private RemoveFromCart $removeFromCart,
    ) {}

    public function index(): Response
    {
        $cart = CartSession::current();

        $items = [];
        $total = null;

        if ($cart) {
            /** @var Collection<int, CartLine> $lines */
            $lines = $cart->lines()->with('purchasable.product')->get();

            foreach ($lines as $line) {
                /** @var ProductVariant $variant */
                $variant = $line->purchasable;

                /** @var Product $product */
                $product = $variant->product;

                $items[] = [
                    'cartLineId' => (int) $line->id,
                    'variantId' => (int) $variant->id,
                    'productName' => $product->translateAttribute('name'),
                    'variantOptions' => '',
                    'sku' => $variant->sku,
                    'quantity' => (int) $line->quantity,
                    'unitPrice' => $line->unitPrice?->value,
                    'subTotal' => $line->subTotal?->value,
                ];
            }

            $total = $cart->total?->value;
        }

        return Inertia::render('cart/index', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function store(AddToCartRequest $request): RedirectResponse
    {
        $this->addToCart->handle(
            variantId: $request->integer('variantId'),
            quantity: $request->integer('quantity'),
        );

        return back();
    }

    public function update(UpdateCartItemRequest $request, int $cartLineId): RedirectResponse
    {
        $this->updateCartItem->handle(
            cartLineId: $cartLineId,
            quantity: $request->integer('quantity'),
        );

        return back();
    }

    public function destroy(int $cartLineId): RedirectResponse
    {
        $this->removeFromCart->handle(cartLineId: $cartLineId);

        return back();
    }
}
