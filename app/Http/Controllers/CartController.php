<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AddToCart;
use App\Actions\ApplyCoupon;
use App\Actions\RemoveFromCart;
use App\Actions\UpdateCartItem;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\ApplyCouponRequest;
use App\Http\Requests\UpdateCartItemRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\CartSession;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\CartLine;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

final readonly class CartController
{
    public function __construct(
        private AddToCart $addToCart,
        private UpdateCartItem $updateCartItem,
        private RemoveFromCart $removeFromCart,
        private ApplyCoupon $applyCoupon,
    ) {}

    public function index(): Response
    {
        $cart = CartSession::current();

        /** @var array<int, array{cartLineId: int, variantId: int, productName: string, variantOptions: string, sku: string|null, quantity: int, unitPrice: int|null, subTotal: int|null}> $items */
        $items = [];
        /** @var int|null $subTotal */
        $subTotal = null;
        /** @var int|null $total */
        $total = null;
        /** @var string|null $couponCode */
        $couponCode = null;
        /** @var int|null $discountTotal */
        $discountTotal = null;
        /** @var array<int, array{identifier: string, name: string, description: string, price: int}> $shippingOptions */
        $shippingOptions = [];

        if ($cart) {
            $cart = $cart->calculate();

            $cart->lines->loadMissing('purchasable.product');
            /** @var Collection<int, CartLine> $lines */
            $lines = $cart->lines;

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
            $subTotal = $cart->subTotal?->value;
            $couponCode = $cart->coupon_code;
            $discountTotal = $cart->discountTotal?->value;

            /** @var Collection<int, ShippingOption> $rawShippingOptions */
            $rawShippingOptions = ShippingManifest::getOptions($cart);
            $shippingOptions = $rawShippingOptions
                ->map(fn (ShippingOption $option): array => [
                    'identifier' => $option->getIdentifier(),
                    'name' => $option->getName(),
                    'description' => $option->getDescription(),
                    'price' => $option->getPrice()->value,
                ])
                ->values()
                ->all();
        }

        return Inertia::render('cart/index', [
            'items' => $items,
            'subTotal' => $subTotal,
            'total' => $total,
            'couponCode' => $couponCode,
            'discountTotal' => $discountTotal,
            'shippingOptions' => $shippingOptions,
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

    public function applyCoupon(ApplyCouponRequest $request): RedirectResponse
    {
        $cart = CartSession::current();

        if (! $cart) {
            return back();
        }

        $applied = $this->applyCoupon->handle($cart, $request->string('couponCode')->value());

        if (! $applied) {
            return back()->withErrors(['couponCode' => 'このクーポンコードは無効です。']);
        }

        return back();
    }

    public function removeCoupon(): RedirectResponse
    {
        $cart = CartSession::current();

        if ($cart) {
            $cart->coupon_code = null;
            $cart->save();
        }

        return back();
    }
}
