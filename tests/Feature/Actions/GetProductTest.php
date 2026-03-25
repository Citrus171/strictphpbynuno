<?php

declare(strict_types=1);

use App\Actions\GetProduct;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductAssociation;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;
use Lunar\Models\ProductVariant;
use Lunar\Models\Url;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Language::factory()->create(['default' => true]);
});

it('公開済み商品のslugを指定した時、商品を取得できること', function (): void {
    $product = Product::factory()->create(['status' => 'published']);

    Url::factory()->create([
        'language_id' => Language::query()->where('default', true)->value('id'),
        'element_type' => Product::morphName(),
        'element_id' => $product->id,
        'slug' => 'sample-product',
        'default' => true,
    ]);

    $result = resolve(GetProduct::class)->handle('sample-product');

    expect($result->is($product))->toBeTrue();
});

it('下書き商品のslugを指定した時、商品が見つからないこと', function (): void {
    $product = Product::factory()->create(['status' => 'draft']);

    Url::factory()->create([
        'language_id' => Language::query()->where('default', true)->value('id'),
        'element_type' => Product::morphName(),
        'element_id' => $product->id,
        'slug' => 'draft-product',
        'default' => true,
    ]);

    expect(fn (): Product => resolve(GetProduct::class)->handle('draft-product'))
        ->toThrow(ModelNotFoundException::class);
});

it('バリアントのSKUと在庫数が取得できること', function (): void {
    $product = Product::factory()->create(['status' => 'published']);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'TEST-SKU-001',
        'stock' => 5,
        'purchasable' => 'in_stock',
    ]);

    Url::factory()->create([
        'language_id' => Language::query()->where('default', true)->value('id'),
        'element_type' => Product::morphName(),
        'element_id' => $product->id,
        'slug' => 'stock-product',
        'default' => true,
    ]);

    $result = resolve(GetProduct::class)->handle('stock-product');
    $variant = $result->variants->first();

    expect($variant->sku)->toBe('TEST-SKU-001')
        ->and($variant->stock)->toBe(5)
        ->and($variant->purchasable)->toBe('in_stock')
        ->and($variant->relationLoaded('prices'))->toBeTrue();
});

it('バリアントのオプション情報が取得できること', function (): void {
    $option = ProductOption::factory()->create(['name' => ['en' => 'Size']]);
    $value = ProductOptionValue::factory()->create([
        'product_option_id' => $option->id,
        'name' => ['en' => 'M'],
    ]);

    $product = Product::factory()->create(['status' => 'published']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
    $variant->values()->attach($value);

    Url::factory()->create([
        'language_id' => Language::query()->where('default', true)->value('id'),
        'element_type' => Product::morphName(),
        'element_id' => $product->id,
        'slug' => 'option-product',
        'default' => true,
    ]);

    $result = resolve(GetProduct::class)->handle('option-product');
    $firstVariant = $result->variants->first();

    expect($firstVariant->relationLoaded('values'))->toBeTrue()
        ->and($firstVariant->values)->toHaveCount(1)
        ->and($firstVariant->values->first()->product_option_id)->toBe($option->id)
        ->and($firstVariant->values->first()->relationLoaded('option'))->toBeTrue();
});

it('関連商品が取得できること', function (): void {
    $product = Product::factory()->create(['status' => 'published']);
    $related = Product::factory()->create(['status' => 'published']);

    ProductAssociation::factory()->create([
        'product_parent_id' => $product->id,
        'product_target_id' => $related->id,
        'type' => 'cross-sell',
    ]);

    Url::factory()->create([
        'language_id' => Language::query()->where('default', true)->value('id'),
        'element_type' => Product::morphName(),
        'element_id' => $product->id,
        'slug' => 'assoc-product',
        'default' => true,
    ]);

    $result = resolve(GetProduct::class)->handle('assoc-product');

    expect($result->relationLoaded('associations'))->toBeTrue()
        ->and($result->associations)->toHaveCount(1)
        ->and($result->associations->first()->relationLoaded('target'))->toBeTrue()
        ->and($result->associations->first()->target->id)->toBe($related->id);
});
