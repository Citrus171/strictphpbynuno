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
    Currency::factory()->create(['default' => true]);
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
 * テスト用に価格付きバリアントを作成してカートに追加するヘルパー
 */
function addItemToCart(int $price = 1000): void
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
}

// ─── Slice 1: カートが空の場合のリダイレクト ──────────────────────────────────

it('カートが空の時、GET /checkout/addressはカートページにリダイレクトされること', function (): void {
    $response = $this->get(route('checkout.address'));

    $response->assertRedirect(route('cart.index'));
});

it('カートが空の時、GET /checkout/shippingはカートページにリダイレクトされること', function (): void {
    $response = $this->get(route('checkout.shipping'));

    $response->assertRedirect(route('cart.index'));
});

// ─── Slice 6: カートなし時の POST リダイレクト ────────────────────────────────

it('カートが空の時、POST /checkout/addressはカートページにリダイレクトされること', function (): void {
    $response = $this->post(route('checkout.address.store'), [
        'first_name' => '山田太郎',
        'postcode' => '100-0001',
        'state' => '東京都',
        'city' => '千代田区',
        'line_one' => '千代田1-1-1',
        'contact_phone' => '03-1234-5678',
    ]);

    $response->assertRedirect(route('cart.index'));
});

it('カートが空の時、POST /checkout/shippingはカートページにリダイレクトされること', function (): void {
    $response = $this->post(route('checkout.shipping.store'), [
        'identifier' => 'flat_rate_standard',
    ]);

    $response->assertRedirect(route('cart.index'));
});

// ─── Slice 3: 住所入力フォームの送信 ──────────────────────────────────────────

it('有効な住所データをPOSTした時、Checkout/Shippingにリダイレクトされること', function (): void {
    addItemToCart();

    $response = $this->post(route('checkout.address.store'), [
        'first_name' => '山田太郎',
        'postcode' => '100-0001',
        'state' => '東京都',
        'city' => '千代田区',
        'line_one' => '千代田1-1-1',
        'contact_phone' => '03-1234-5678',
    ]);

    $response->assertRedirect(route('checkout.shipping'));
});

it('有効な住所データをPOSTした時、カートに住所が保存されること', function (): void {
    addItemToCart();

    $this->post(route('checkout.address.store'), [
        'first_name' => '山田太郎',
        'postcode' => '100-0001',
        'state' => '東京都',
        'city' => '千代田区',
        'line_one' => '千代田1-1-1',
        'contact_phone' => '03-1234-5678',
    ]);

    $this->assertDatabaseHas('lunar_cart_addresses', [
        'first_name' => '山田太郎',
        'postcode' => '100-0001',
        'type' => 'shipping',
    ]);
});

// ─── Slice 5: 配送方法の選択 ──────────────────────────────────────────────────

it('有効な配送方法identifierをPOSTした時、カートに保存されリダイレクトされること', function (): void {
    addItemToCart();

    $this->post(route('checkout.address.store'), [
        'first_name' => '山田太郎',
        'postcode' => '100-0001',
        'state' => '東京都',
        'city' => '千代田区',
        'line_one' => '千代田1-1-1',
        'contact_phone' => '03-1234-5678',
    ]);

    $response = $this->post(route('checkout.shipping.store'), [
        'identifier' => 'flat_rate_standard',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('lunar_cart_addresses', [
        'shipping_option' => 'flat_rate_standard',
        'type' => 'shipping',
    ]);
});

it('identifierが欠けている時、POST /checkout/shippingがバリデーションエラーを返すこと', function (): void {
    addItemToCart();

    $response = $this->post(route('checkout.shipping.store'), []);

    $response->assertSessionHasErrors('identifier');
});

// ─── Slice 4: 住所バリデーション ──────────────────────────────────────────────

it('必須フィールドが欠けている時、POST /checkout/addressがバリデーションエラーを返すこと', function (string $field): void {
    addItemToCart();

    $valid = [
        'first_name' => '山田太郎',
        'postcode' => '100-0001',
        'state' => '東京都',
        'city' => '千代田区',
        'line_one' => '千代田1-1-1',
        'contact_phone' => '03-1234-5678',
    ];

    unset($valid[$field]);

    $response = $this->post(route('checkout.address.store'), $valid);

    $response->assertSessionHasErrors($field);
})->with([
    '氏名' => 'first_name',
    '郵便番号' => 'postcode',
    '都道府県' => 'state',
    '市区町村' => 'city',
    '番地' => 'line_one',
    '電話番号' => 'contact_phone',
]);

// ─── Slice 2: カートありの場合ページ表示 ───────────────────────────────────────

it('カートに商品がある時、GET /checkout/addressがアドレスページを表示すること', function (): void {
    addItemToCart();

    $response = $this->get(route('checkout.address'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page->component('checkout/address'));
});

it('カートに商品がある時、GET /checkout/shippingが配送オプションを含むページを表示すること', function (): void {
    addItemToCart();

    $response = $this->get(route('checkout.shipping'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('checkout/shipping')
            ->has('shippingOptions'));
});

// ─── Slice 7: 注文確認・注文完了 ──────────────────────────────────────────────

/**
 * 住所と配送方法を設定するヘルパー
 */
function setupCheckoutAddress(): void
{
    test()->post(route('checkout.address.store'), [
        'first_name' => '山田太郎',
        'postcode' => '100-0001',
        'state' => '東京都',
        'city' => '千代田区',
        'line_one' => '千代田1-1-1',
        'contact_phone' => '03-1234-5678',
    ]);
}

function setupCheckoutShipping(): void
{
    test()->post(route('checkout.shipping.store'), [
        'identifier' => 'flat_rate_standard',
    ]);
}

it('カートに商品と配送情報がある時、GET /checkout/confirmが確認ページを表示すること', function (): void {
    addItemToCart();

    $this->post(route('checkout.address.store'), [
        'first_name' => '山田太郎',
        'postcode' => '100-0001',
        'state' => '東京都',
        'city' => '千代田区',
        'line_one' => '千代田1-1-1',
        'contact_phone' => '03-1234-5678',
    ]);

    $response = $this->get(route('checkout.confirm'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('checkout/confirm')
            ->has('lines')
            ->has('subTotal')
            ->has('total'));
});

it('有効なカートの時、POST /checkout/confirmで注文が作成されCheckout/Completeにリダイレクトされること', function (): void {
    addItemToCart();
    setupCheckoutAddress();
    setupCheckoutShipping();

    $response = $this->post(route('checkout.confirm.store'));

    $response->assertRedirect();
    $this->assertDatabaseCount('lunar_orders', 1);
});

it('有効なカートの時、POST /checkout/confirmで注文確定後にカートがクリアされること', function (): void {
    addItemToCart();
    setupCheckoutAddress();
    setupCheckoutShipping();

    $this->post(route('checkout.confirm.store'));

    $this->assertNull(Lunar\Facades\CartSession::current());
});

it('有効な注文の時、GET /checkout/completeが注文完了ページを表示すること', function (): void {
    addItemToCart();
    setupCheckoutAddress();
    setupCheckoutShipping();

    $response = $this->post(route('checkout.confirm.store'));
    $orderId = Lunar\Models\Order::query()->first()->id;

    $this->get(route('checkout.complete', ['order' => $orderId]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('checkout/complete')
            ->has('orderReference')
            ->has('total'));
});

it('カートが空の時、GET /checkout/confirmはカートページにリダイレクトされること', function (): void {
    $response = $this->get(route('checkout.confirm'));

    $response->assertRedirect(route('cart.index'));
});

it('カートが空の時、POST /checkout/confirmはカートページにリダイレクトされること', function (): void {
    $response = $this->post(route('checkout.confirm.store'));

    $response->assertRedirect(route('cart.index'));
});
