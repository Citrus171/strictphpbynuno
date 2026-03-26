<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Language;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Language::factory()->create(['default' => true]);
});

it('全ストアフロントページでJavaScriptエラーが発生しないこと', function (): void {
    $pages = visit(['/products', '/cart']);

    $pages->assertNoSmoke();
});
