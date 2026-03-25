<?php

declare(strict_types=1);

use App\Actions\ApplyCoupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DiscountTypes\AmountOff;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Discount;
use Lunar\Models\Language;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Lunar\Models\TaxZone;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Language::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true, 'code' => 'JPY']);
    Channel::factory()->create(['default' => true]);

    $taxClass = TaxClass::factory()->create(['name' => 'Default']);
    $taxZone = TaxZone::factory()->create(['default' => true]);
    $taxRate = TaxRate::factory()->create(['tax_zone_id' => $taxZone->id]);
    TaxRateAmount::factory()->create([
        'tax_rate_id' => $taxRate->id,
        'tax_class_id' => $taxClass->id,
        'percentage' => 0,
    ]);
});

/**
 * テスト用にデフォルトの Currency と Channel を使ってカートを作るヘルパー
 */
function createTestCart(): Cart
{
    $currency = Currency::query()->where('default', true)->firstOrFail();
    $channel = Channel::query()->where('default', true)->firstOrFail();

    return Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);
}

it('有効なクーポンコードの時、カートにクーポンが設定されtrueが返ること', function (): void {
    $cart = createTestCart();

    Discount::factory()->create([
        'coupon' => 'SAVE10',
        'type' => AmountOff::class,
        'starts_at' => now()->subDay(),
        'data' => ['fixed_value' => false, 'percentage' => 10],
    ]);

    $result = resolve(ApplyCoupon::class)->handle($cart, 'SAVE10');

    expect($result)->toBeTrue();
    $this->assertDatabaseHas('lunar_carts', [
        'id' => $cart->id,
        'coupon_code' => 'SAVE10',
    ]);
});

it('無効なクーポンコードの時、カートが変更されずfalseが返ること', function (): void {
    $cart = createTestCart();

    $result = resolve(ApplyCoupon::class)->handle($cart, 'INVALID');

    expect($result)->toBeFalse();
    $this->assertDatabaseHas('lunar_carts', [
        'id' => $cart->id,
        'coupon_code' => null,
    ]);
});

it('期限切れクーポンの時、falseが返ること', function (): void {
    $cart = createTestCart();

    Discount::factory()->create([
        'coupon' => 'EXPIRED',
        'type' => AmountOff::class,
        'starts_at' => now()->subMonth(),
        'ends_at' => now()->subDay(),
        'data' => ['fixed_value' => false, 'percentage' => 10],
    ]);

    $result = resolve(ApplyCoupon::class)->handle($cart, 'EXPIRED');

    expect($result)->toBeFalse();
});
