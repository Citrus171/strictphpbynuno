<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\FieldTypes\Text;
use Lunar\Models\Channel;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Lunar\Models\TaxZone;
use Lunar\Models\Url;

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

it('商品一覧から注文完了まで全購買フローが動作すること', function (): void {
    $currency = Currency::query()->where('default', true)->firstOrFail();
    $taxClass = TaxClass::query()->where('name', 'Default')->firstOrFail();

    $product = Product::factory()->create([
        'status' => 'published',
        'attribute_data' => collect([
            'name' => new Text('E2Eテスト商品'),
            'description' => new Text('テスト用の商品です'),
        ]),
    ]);

    Url::factory()->create([
        'language_id' => Language::query()->where('default', true)->value('id'),
        'element_type' => Product::morphName(),
        'element_id' => $product->id,
        'slug' => 'e2e-test-product',
        'default' => true,
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'tax_class_id' => $taxClass->id,
        'stock' => 10,
        'purchasable' => 'in_stock',
    ]);

    Price::query()->create([
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'price' => 2000,
        'min_quantity' => 1,
    ]);

    $page = visit('/products');

    $page->assertSee('E2Eテスト商品')
        ->click('E2Eテスト商品')
        ->assertSee('E2Eテスト商品')
        ->assertSee('カートに追加')
        ->click('カートに追加')
        ->navigate('/cart')
        ->assertSee('E2Eテスト商品')
        ->assertSee('レジに進む')
        ->click('レジに進む')
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
        ->click('button[type="submit"]')
        ->assertSee('ご注文ありがとうございます')
        ->assertSee('注文番号')
        ->assertNoJavascriptErrors();
});
