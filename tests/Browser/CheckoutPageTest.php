<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Channel;
use Lunar\Models\Country;
use Lunar\Models\Currency;
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
    Country::factory()->create(['iso2' => 'JP', 'name' => 'Japan']);

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
 * テスト用バリアントを作成してカートに追加するヘルパー
 */
function addCheckoutTestItem(int $price = 1000): ProductVariant
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

    test()->post(route('cart.items.store'), ['variantId' => $variant->id, 'quantity' => 1]);

    return $variant;
}

it('カートが空の時、チェックアウトページがカートにリダイレクトされること', function (): void {
    $page = visit('/checkout/address');

    $page->assertSee('カート')
        ->assertNoJavascriptErrors();
});

it('カートページに「レジに進む」ボタンが表示されること', function (): void {
    addCheckoutTestItem(1500);

    $page = visit('/cart');

    $page->assertSee('レジに進む')
        ->assertNoJavascriptErrors();
});

it('チェックアウト住所ページが正しく表示されること', function (): void {
    addCheckoutTestItem(2000);

    $page = visit('/checkout/address');

    $page->assertSee('配送先住所')
        ->assertSee('氏名')
        ->assertSee('郵便番号')
        ->assertSee('都道府県')
        ->assertSee('市区町村')
        ->assertSee('番地')
        ->assertSee('電話番号')
        ->assertNoJavascriptErrors();
});

it('住所を入力して配送方法ページに進めること', function (): void {
    addCheckoutTestItem(2000);

    $page = visit('/checkout/address');

    $page->assertSee('配送先住所')
        ->fill('input[name="first_name"]', '山田太郎')
        ->fill('input[name="postcode"]', '100-0001')
        ->fill('input[name="state"]', '東京都')
        ->fill('input[name="city"]', '千代田区')
        ->fill('input[name="line_one"]', '千代田1-1-1')
        ->fill('input[name="contact_phone"]', '03-1234-5678')
        ->click('button[type="submit"]')
        ->assertSee('配送方法を選択')
        ->assertSee('標準配送')
        ->assertSee('速達便')
        ->assertNoJavascriptErrors();
});

it('配送方法を選択して注文確認ページに進めること', function (): void {
    addCheckoutTestItem(2000);

    $page = visit('/checkout/address');

    $page->assertSee('配送先住所')
        ->fill('input[name="first_name"]', '山田太郎')
        ->fill('input[name="postcode"]', '100-0001')
        ->fill('input[name="state"]', '東京都')
        ->fill('input[name="city"]', '千代田区')
        ->fill('input[name="line_one"]', '千代田1-1-1')
        ->fill('input[name="contact_phone"]', '03-1234-5678')
        ->click('button[type="submit"]')
        ->assertSee('配送方法を選択')
        ->click('input[value="flat_rate_standard"]')
        ->click('button[type="submit"]')
        ->assertSee('注文内容の確認')
        ->assertSee('商品一覧')
        ->assertSee('合計')
        ->assertSee('注文を確定する')
        ->assertNoJavascriptErrors();
});

it('注文確認から注文完了まで全フローが動作すること', function (): void {
    addCheckoutTestItem(3000);

    $page = visit('/checkout/address');

    $page->fill('input[name="first_name"]', '山田太郎')
        ->fill('input[name="postcode"]', '100-0001')
        ->fill('input[name="state"]', '東京都')
        ->fill('input[name="city"]', '千代田区')
        ->fill('input[name="line_one"]', '千代田1-1-1')
        ->fill('input[name="contact_phone"]', '03-1234-5678')
        ->click('button[type="submit"]')
        ->click('input[value="flat_rate_standard"]')
        ->click('button[type="submit"]')
        ->assertSee('注文内容の確認')
        ->click('button[type="submit"]')
        ->assertSee('ご注文ありがとうございます')
        ->assertSee('注文番号')
        ->assertNoJavascriptErrors();
});
