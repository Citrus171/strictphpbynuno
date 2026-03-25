<?php

declare(strict_types=1);

use App\Shipping\FlatRateShipping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Language;

uses(RefreshDatabase::class);

it('デフォルト通貨が存在しない時、nextを呼び出してカートを返すこと', function (): void {
    // Currencyを一切作成しない → $currency が null → 早期リターン
    $cart = Mockery::mock(Cart::class);

    $shipping = new FlatRateShipping();
    $nextCalled = false;
    $next = function (Cart $c) use (&$nextCalled, $cart): Cart {
        $nextCalled = true;

        return $cart;
    };

    $result = $shipping->handle($cart, $next);

    expect($nextCalled)->toBeTrue();
    expect($result)->toBe($cart);
});

it('税クラスが存在しない時、nextを呼び出してカートを返すこと', function (): void {
    Language::factory()->create(['default' => true]);
    Channel::factory()->create(['default' => true]);
    Queue::fake();
    Currency::factory()->create(['default' => true]);
    // TaxClass未作成 → $taxClass が null → 早期リターン
    $cart = Mockery::mock(Cart::class);

    $shipping = new FlatRateShipping();
    $nextCalled = false;
    $next = function (Cart $c) use (&$nextCalled, $cart): Cart {
        $nextCalled = true;

        return $cart;
    };

    $result = $shipping->handle($cart, $next);

    expect($nextCalled)->toBeTrue();
    expect($result)->toBe($cart);
});
