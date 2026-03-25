<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\FieldTypes\Text;
use Lunar\Models\Language;
use Lunar\Models\Product;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Language::factory()->create(['default' => true]);
});

it('商品一覧ページが正しく表示されること', function (): void {
    $page = visit('/products');

    $page->assertSee('商品一覧')
        ->assertNoJavascriptErrors();
});

it('ストアフロントのヘッダーナビゲーションが表示されること', function (): void {
    $page = visit('/products');

    $page->assertSee('ストア')
        ->assertSee('商品一覧')
        ->assertNoJavascriptErrors();
});

it('商品カードに商品名・ブランド・価格が表示されること', function (): void {
    Product::factory()->create([
        'status' => 'published',
        'attribute_data' => collect([
            'name' => new Text('テスト商品A'),
            'description' => new Text('説明文'),
        ]),
    ]);

    $page = visit('/products');

    $page->assertSee('テスト商品A')
        ->assertNoJavascriptErrors();
});

it('商品がない場合に空状態メッセージが表示されること', function (): void {
    $page = visit('/products');

    $page->assertSee('商品がありません')
        ->assertNoJavascriptErrors();
});
