<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SelectShippingMethodRequest;
use App\Http\Requests\StoreCheckoutAddressRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\CartSession;
use Lunar\Facades\ShippingManifest;

final readonly class CheckoutController
{
    public function address(): Response|RedirectResponse
    {
        $cart = CartSession::current();

        if (! $cart || $cart->lines->isEmpty()) {
            return to_route('cart.index');
        }

        return Inertia::render('checkout/address');
    }

    public function storeAddress(StoreCheckoutAddressRequest $request): RedirectResponse
    {
        $cart = CartSession::current();

        if (! $cart || $cart->lines->isEmpty()) {
            return to_route('cart.index');
        }

        $cart->setShippingAddress([
            'first_name' => $request->string('first_name')->value(),
            'postcode' => $request->string('postcode')->value(),
            'state' => $request->string('state')->value(),
            'city' => $request->string('city')->value(),
            'line_one' => $request->string('line_one')->value(),
            'contact_phone' => $request->string('contact_phone')->value(),
        ]);

        return to_route('checkout.shipping');
    }

    public function shipping(): Response|RedirectResponse
    {
        $cart = CartSession::current();

        if (! $cart || $cart->lines->isEmpty()) {
            return to_route('cart.index');
        }

        $cart = $cart->calculate();

        /** @var Collection<int, ShippingOption> $rawOptions */
        $rawOptions = ShippingManifest::getOptions($cart);
        $shippingOptions = $rawOptions
            ->map(fn (ShippingOption $option): array => [
                'identifier' => $option->getIdentifier(),
                'name' => $option->getName(),
                'description' => $option->getDescription(),
                'price' => $option->getPrice()->value,
            ])
            ->values()
            ->all();

        return Inertia::render('checkout/shipping', [
            'shippingOptions' => $shippingOptions,
        ]);
    }

    public function storeShipping(SelectShippingMethodRequest $request): RedirectResponse
    {
        $cart = CartSession::current();

        if (! $cart || $cart->lines->isEmpty()) {
            return to_route('cart.index');
        }

        $shippingAddress = $cart->shippingAddress;

        if ($shippingAddress) {
            $shippingAddress->update([
                'shipping_option' => $request->string('identifier')->value(),
            ]);
        }

        return to_route('checkout.shipping');
    }
}
