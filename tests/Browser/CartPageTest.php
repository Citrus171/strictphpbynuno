<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DiscountTypes\AmountOff;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Discount;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
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
 * テスト用に価格付きバリアントを作成するヘルパー
 */
function createBrowserTestVariant(int $price = 1000): ProductVariant
{
    $currency = Currency::query()->where('default', true)->firstOrFail();
    $taxClass = TaxClass::query()->where('name', 'Default')->firstOrFail();

    $variant = ProductVariant::factory()->create([
        'tax_class_id' => $taxClass->id,
        'stock' => 10,
        'purchasable' => 'in_stock',
    ]);

    Price::query()->create([
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'price' => $price,
        'min_quantity' => 1,
    ]);

    return $variant;
}

it('カートページが正しく表示されること', function (): void {
    $page = visit('/cart');

    $page->assertSee('カート')
        ->assertNoJavascriptErrors();
});

it('カートが空の時に空メッセージが表示されること', function (): void {
    $page = visit('/cart');

    $page->assertSee('カートは空です')
        ->assertNoJavascriptErrors();
});

it('商品追加後に送料オプションが表示されること', function (): void {
    $variant = createBrowserTestVariant(1500);

    $this->post(route('cart.items.store'), [
        'variantId' => $variant->id,
        'quantity' => 1,
    ]);

    $page = visit('/cart');

    $page->assertSee('送料')
        ->assertSee('標準配送')
        ->assertNoJavascriptErrors();
});

it('クーポン入力フォームが表示されること', function (): void {
    $variant = createBrowserTestVariant(2000);

    // まずカートに商品を追加するためAPIを使う
    $this->post(route('cart.items.store'), [
        'variantId' => $variant->id,
        'quantity' => 1,
    ]);

    $page = visit('/cart');

    $page->assertSee('クーポンコード')
        ->assertNoJavascriptErrors();
});

it('有効なクーポンを適用した時に割引が表示されること', function (): void {
    $variant = createBrowserTestVariant(3000);

    Discount::factory()->create([
        'coupon' => 'BROWSER10',
        'type' => AmountOff::class,
        'starts_at' => now()->subDay(),
        'data' => ['fixed_value' => false, 'percentage' => 10],
    ]);

    $this->post(route('cart.items.store'), [
        'variantId' => $variant->id,
        'quantity' => 1,
    ]);

    $page = visit('/cart');

    $page->assertSee('クーポンコード')
        ->fill('input[name="couponCode"]', 'BROWSER10')
        ->click('適用')
        ->assertSee('BROWSER10')
        ->assertNoJavascriptErrors();
});
