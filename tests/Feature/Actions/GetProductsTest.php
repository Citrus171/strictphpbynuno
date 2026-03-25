<?php

declare(strict_types=1);

use App\Actions\GetProducts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Lunar\Models\Language;
use Lunar\Models\Product;

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
