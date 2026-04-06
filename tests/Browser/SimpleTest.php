<?php

declare(strict_types=1);

it('トップページが表示されること', function (): void {
    $page = visit('/');

    $page->assertSee('Laravel');
    $page->assertNoJavascriptErrors();
    $page->assertNoSmoke();
});
