<?php

declare(strict_types=1);

use App\Actions\GetProduct;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Language;
use Lunar\Models\Product;
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
