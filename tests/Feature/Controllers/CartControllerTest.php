<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\CartLine;
use Lunar\Models\Channel;
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
function createVariantWithPrice(int $price = 1000, int $stock = 10): ProductVariant
{
    $currency = Currency::query()->where('default', true)->firstOrFail();
    $taxClass = TaxClass::query()->where('name', 'Default')->firstOrFail();

    $variant = ProductVariant::factory()->create([
        'tax_class_id' => $taxClass->id,
        'stock' => $stock,
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

// ─── Slice 1: GET /cart ───────────────────────────────────────────────────────

it('/cartにアクセスした時、カートページが表示されること', function (): void {
    $response = $this->get(route('cart.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('cart/index')
            ->has('items')
            ->has('total'));
});

it('カートが空の時、itemsが空配列であること', function (): void {
    $response = $this->get(route('cart.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('items', []));
});

// ─── Slice 2 & 3: POST /cart/items ────────────────────────────────────────────

it('バリアントIDと数量を送信した時、カートにアイテムが追加されること', function (): void {
    $variant = createVariantWithPrice(price: 1500, stock: 10);

    $response = $this->post(route('cart.items.store'), [
        'variantId' => $variant->id,
        'quantity' => 2,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('lunar_cart_lines', [
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => 2,
    ]);
});

it('同じバリアントを2回追加した時、数量が加算されること', function (): void {
    $variant = createVariantWithPrice(price: 1000, stock: 10);

    $this->post(route('cart.items.store'), ['variantId' => $variant->id, 'quantity' => 1]);
    $this->post(route('cart.items.store'), ['variantId' => $variant->id, 'quantity' => 2]);

    $this->assertDatabaseHas('lunar_cart_lines', [
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => 3,
    ]);
    $this->assertDatabaseCount('lunar_cart_lines', 1);
});

// ─── Slice 4: PATCH /cart/items/{cartLineId} ──────────────────────────────────

it('カートラインIDと数量を送信した時、数量が更新されること', function (): void {
    $variant = createVariantWithPrice(price: 1000, stock: 10);

    $this->post(route('cart.items.store'), ['variantId' => $variant->id, 'quantity' => 1]);

    $cartLine = CartLine::query()->where('purchasable_id', $variant->id)->firstOrFail();

    $response = $this->patch(route('cart.items.update', $cartLine->id), [
        'quantity' => 5,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('lunar_cart_lines', [
        'id' => $cartLine->id,
        'quantity' => 5,
    ]);
});

// ─── Slice 5: DELETE /cart/items/{cartLineId} ─────────────────────────────────

it('カートラインIDを指定した時、アイテムが削除されること', function (): void {
    $variant = createVariantWithPrice(price: 1000, stock: 10);

    $this->post(route('cart.items.store'), ['variantId' => $variant->id, 'quantity' => 1]);

    $cartLine = CartLine::query()->where('purchasable_id', $variant->id)->firstOrFail();

    $response = $this->delete(route('cart.items.destroy', $cartLine->id));

    $response->assertRedirect();

    $this->assertDatabaseMissing('lunar_cart_lines', ['id' => $cartLine->id]);
});

// ─── Slice 6: カートデータ構造 ────────────────────────────────────────────────

it('カートに追加後、GETでitemsに商品名・数量・小計が含まれること', function (): void {
    $variant = createVariantWithPrice(price: 2000, stock: 10);

    $this->post(route('cart.items.store'), ['variantId' => $variant->id, 'quantity' => 2]);

    $response = $this->get(route('cart.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('items', 1)
            ->has('items.0.cartLineId')
            ->has('items.0.variantId')
            ->has('items.0.productName')
            ->has('items.0.quantity')
            ->has('items.0.unitPrice')
            ->has('items.0.subTotal')
            ->where('items.0.quantity', 2));
});

it('カートに追加後、GETでtotalが含まれること', function (): void {
    $variant = createVariantWithPrice(price: 3000, stock: 10);

    $this->post(route('cart.items.store'), ['variantId' => $variant->id, 'quantity' => 1]);

    $response = $this->get(route('cart.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->whereNot('total', null));
});

// ─── Slice 7: クーポン適用エンドポイント ──────────────────────────────────────

it('有効なクーポンコードを送信した時、カートに適用されリダイレクトされること', function (): void {
    $variant = createVariantWithPrice(price: 1000, stock: 10);
    $this->post(route('cart.items.store'), ['variantId' => $variant->id, 'quantity' => 1]);

    Lunar\Models\Discount::factory()->create([
        'coupon' => 'VALID10',
        'type' => Lunar\DiscountTypes\AmountOff::class,
        'starts_at' => now()->subDay(),
        'data' => ['fixed_value' => false, 'percentage' => 10],
    ]);

    $response = $this->post(route('cart.coupon.store'), [
        'couponCode' => 'VALID10',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('lunar_carts', ['coupon_code' => 'VALID10']);
});

it('無効なクーポンコードを送信した時、エラーがセッションに入ること', function (): void {
    $variant = createVariantWithPrice(price: 1000, stock: 10);
    $this->post(route('cart.items.store'), ['variantId' => $variant->id, 'quantity' => 1]);

    $response = $this->post(route('cart.coupon.store'), [
        'couponCode' => 'BADCODE',
    ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('couponCode');
});

it('クーポン適用後にGETするとcouponCodeとdiscountTotalが含まれること', function (): void {
    $variant = createVariantWithPrice(price: 1000, stock: 10);
    $this->post(route('cart.items.store'), ['variantId' => $variant->id, 'quantity' => 1]);

    Lunar\Models\Discount::factory()->create([
        'coupon' => 'DISC20',
        'type' => Lunar\DiscountTypes\AmountOff::class,
        'starts_at' => now()->subDay(),
        'data' => ['fixed_value' => false, 'percentage' => 20],
    ]);
    $this->post(route('cart.coupon.store'), ['couponCode' => 'DISC20']);

    $response = $this->get(route('cart.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('couponCode')
            ->has('discountTotal')
            ->where('couponCode', 'DISC20'));
});

it('クーポンが未適用の時、GETでcouponCodeがnullであること', function (): void {
    $response = $this->get(route('cart.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('couponCode', null));
});

// ─── Slice 8: 送料オプション ───────────────────────────────────────────────────

it('GETでshippingOptionsが配列として返されること', function (): void {
    $response = $this->get(route('cart.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('shippingOptions'));
});

it('送料オプションが登録されている時、identifier・name・priceが含まれること', function (): void {
    $variant = createVariantWithPrice(price: 1000, stock: 10);
    $this->post(route('cart.items.store'), ['variantId' => $variant->id, 'quantity' => 1]);

    $response = $this->get(route('cart.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('shippingOptions', fn ($options) => $options
                ->each(fn ($option) => $option
                    ->has('identifier')
                    ->has('name')
                    ->has('price')
                    ->etc()
                )
            )
        );
});
