<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AddToCart;
use App\Actions\RemoveFromCart;
use App\Actions\UpdateCartItem;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Facades\CartSession;
use Lunar\Models\CartLine;
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
            /** @var \Illuminate\Database\Eloquent\Collection<int, CartLine> $lines */
            $lines = $cart->lines()->with('purchasable.product')->get();

            $items = $lines->map(function (CartLine $line): array {
                /** @var ProductVariant $variant */
                $variant = $line->purchasable;

                return [
                    'cartLineId' => (int) $line->id,
                    'variantId' => (int) $variant->id,
                    'productName' => $variant->product->translateAttribute('name'),
                    'variantOptions' => '',
                    'sku' => $variant->sku,
                    'quantity' => (int) $line->quantity,
                    'unitPrice' => $line->unitPrice?->value,
                    'subTotal' => $line->subTotal?->value,
                ];
            })->values()->all();

            $total = $cart->total?->value;
        }

        return Inertia::render('cart/index', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function store(AddToCartRequest $request): JsonResponse
    {
        $this->addToCart->handle(
            variantId: $request->integer('variantId'),
            quantity: $request->integer('quantity'),
        );

        return response()->json(['message' => 'Added to cart']);
    }

    public function update(UpdateCartItemRequest $request, int $cartLineId): JsonResponse
    {
        $this->updateCartItem->handle(
            cartLineId: $cartLineId,
            quantity: $request->integer('quantity'),
        );

        return response()->json(['message' => 'Cart updated']);
    }

    public function destroy(int $cartLineId): JsonResponse
    {
        $this->removeFromCart->handle(cartLineId: $cartLineId);

        return response()->json(['message' => 'Item removed']);
    }
}
