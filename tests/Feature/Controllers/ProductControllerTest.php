<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Language;
use Lunar\Models\Product;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Language::factory()->create(['default' => true]);
});

it('/productsにアクセスした時、商品一覧ページが表示されること', function (): void {
    $response = $this->get(route('products.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('products/index')
            ->has('products'));
});

it('公開済み商品の一覧がpropsに含まれること', function (): void {
    Product::factory()->count(3)->create(['status' => 'published']);

    $response = $this->get(route('products.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('products/index')
            ->has('products.data', 3)
            ->has('products.data.0.id')
            ->has('products.data.0.name')
            ->has('products.data.0.brand')
            ->has('products.data.0.price')
            ->has('products.data.0.thumbnail'));
});

it('下書き商品は一覧に含まれないこと', function (): void {
    Product::factory()->count(2)->create(['status' => 'published']);
    Product::factory()->create(['status' => 'draft']);

    $response = $this->get(route('products.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 2));
});

it('ページネーションが動作すること', function (): void {
    Product::factory()->count(15)->create(['status' => 'published']);

    $response = $this->get(route('products.index', ['page' => 2, 'perPage' => 12]));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 3)
            ->where('products.current_page', 2));
});
