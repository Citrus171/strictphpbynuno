<?php

declare(strict_types=1);

use App\Actions\GetProducts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Lunar\Models\Brand;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\TaxClass;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Language::factory()->create(['default' => true]);
});

it('公開済み商品をページネーション付きで取得できること', function (): void {
    Product::factory()->count(3)->create(['status' => 'published']);
    Product::factory()->create(['status' => 'draft']);

    $result = resolve(GetProducts::class)->handle();

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->total())->toBe(3);
});

it('デフォルトで12件ずつ取得すること', function (): void {
    Product::factory()->count(15)->create(['status' => 'published']);

    $result = resolve(GetProducts::class)->handle();

    expect($result->perPage())->toBe(12)
        ->and($result->count())->toBe(12);
});

it('ページ番号を指定して取得できること', function (): void {
    Product::factory()->count(15)->create(['status' => 'published']);

    $result = resolve(GetProducts::class)->handle(perPage: 12, page: 2);

    expect($result->currentPage())->toBe(2)
        ->and($result->count())->toBe(3);
});

/**
 * @param  array<int>  $prices
 */
function createProductWithPrices(array $prices, string $status = 'published'): Product
{
    $currency = Currency::firstOrCreate(['default' => true], Currency::factory()->make()->toArray());
    $taxClass = TaxClass::firstOrCreate(['name' => 'Default'], TaxClass::factory()->make()->toArray());
    $product = Product::factory()->create(['status' => $status]);

    foreach ($prices as $price) {
        $variant = $product->variants()->create([
            'tax_class_id' => $taxClass->id,
            'sku' => fake()->unique()->ean8(),
        ]);
        Price::create([
            'currency_id' => $currency->id,
            'priceable_type' => $variant->getMorphClass(),
            'priceable_id' => $variant->id,
            'price' => $price,
            'min_quantity' => 1,
        ]);
    }

    return $product;
}

it('price_ascを指定した時、価格が安い順で返すこと', function (): void {
    $expensive = createProductWithPrices([3000]);
    $cheap = createProductWithPrices([1000]);
    $middle = createProductWithPrices([2000]);

    $result = resolve(GetProducts::class)->handle(sort: 'price_asc');

    $ids = $result->pluck('id')->all();
    expect($ids)->toBe([$cheap->id, $middle->id, $expensive->id]);
});

it('price_descを指定した時、価格が高い順で返すこと', function (): void {
    $expensive = createProductWithPrices([3000]);
    $cheap = createProductWithPrices([1000]);
    $middle = createProductWithPrices([2000]);

    $result = resolve(GetProducts::class)->handle(sort: 'price_desc');

    $ids = $result->pluck('id')->all();
    expect($ids)->toBe([$expensive->id, $middle->id, $cheap->id]);
});

it('キーワードを指定した時、名前に含む商品のみ返すこと', function (): void {
    Product::factory()->create(['status' => 'published', 'attribute_data' => collect(['name' => new Lunar\FieldTypes\Text('iPhone 15 Pro')])]);
    Product::factory()->create(['status' => 'published', 'attribute_data' => collect(['name' => new Lunar\FieldTypes\Text('iPhone 14')])]);
    Product::factory()->create(['status' => 'published', 'attribute_data' => collect(['name' => new Lunar\FieldTypes\Text('Samsung Galaxy')])]);

    $result = resolve(GetProducts::class)->handle(search: 'iPhone');

    expect($result->total())->toBe(2);
});

it('キーワードが一致しない時、空の結果を返すこと', function (): void {
    Product::factory()->create(['status' => 'published', 'attribute_data' => collect(['name' => new Lunar\FieldTypes\Text('Apple Watch')])]);

    $result = resolve(GetProducts::class)->handle(search: 'Nonexistent');

    expect($result->total())->toBe(0);
});

it('name_ascを指定した時、名前のアルファベット順で返すこと', function (): void {
    $productC = Product::factory()->create(['status' => 'published', 'attribute_data' => collect(['name' => new Lunar\FieldTypes\Text('Cinnamon')])]);
    $productA = Product::factory()->create(['status' => 'published', 'attribute_data' => collect(['name' => new Lunar\FieldTypes\Text('Apple')])]);
    $productB = Product::factory()->create(['status' => 'published', 'attribute_data' => collect(['name' => new Lunar\FieldTypes\Text('Banana')])]);

    $result = resolve(GetProducts::class)->handle(sort: 'name_asc');

    $ids = $result->pluck('id')->all();
    expect($ids)->toBe([$productA->id, $productB->id, $productC->id]);
});

it('コレクションを指定した時、そのコレクションの商品のみ返すこと', function (): void {
    $group = CollectionGroup::factory()->create();
    $collection = Collection::factory()->create(['collection_group_id' => $group->id]);

    $productInCollection = Product::factory()->create(['status' => 'published']);
    $productInCollection->collections()->attach($collection->id);

    Product::factory()->create(['status' => 'published']);

    $result = resolve(GetProducts::class)->handle(collection: $collection->id);

    expect($result->total())->toBe(1);
});

it('ブランドフィルタとソートを組み合わせた時、正しく動作すること', function (): void {
    $brand = Brand::factory()->create();
    $otherBrand = Brand::factory()->create();

    $cheap = createProductWithPrices([1000]);
    $cheap->update(['brand_id' => $brand->id]);

    $expensive = createProductWithPrices([3000]);
    $expensive->update(['brand_id' => $brand->id]);

    createProductWithPrices([500]);  // 別ブランド（除外される）

    $result = resolve(GetProducts::class)->handle(brand: $brand->id, sort: 'price_asc');

    $ids = $result->pluck('id')->all();
    expect($result->total())->toBe(2)
        ->and($ids)->toBe([$cheap->id, $expensive->id]);
});

it('ブランドを指定した時、そのブランドの商品のみ返すこと', function (): void {
    $brand = Brand::factory()->create();
    $otherBrand = Brand::factory()->create();

    Product::factory()->count(2)->create(['status' => 'published', 'brand_id' => $brand->id]);
    Product::factory()->create(['status' => 'published', 'brand_id' => $otherBrand->id]);

    $result = resolve(GetProducts::class)->handle(brand: $brand->id);

    expect($result->total())->toBe(2);
});
